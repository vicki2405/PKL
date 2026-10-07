<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BackupController extends Controller
{
    /**
     * @var array<int, string>
     */
    protected array $tables = [
        'users',
        'password_reset_tokens',
        'roles',
        'permissions',
        'model_has_roles',
        'model_has_permissions',
        'role_has_permissions',
        'jurusans',
        'kelas',
        'siswas',
        'siswa_users',
        'jabatan_pembimbings',
        'pembimbings',
        'pembimbing_users',
        'bengkels',
        'penempatan_p_k_l_s',
        'mutasi_p_k_l_s',
        'monitorings',
        'monitoring_details',
        'monitoring_settings',
        'surat_pengantars',
        'surat_pengantar_siswa',
        'surat_pengantar_template_settings',
        'informasis',
        'rekap_problems',
        'pembekalan_attendances',
        'pembekalan_aktivasis',
        'siswa_karirs',
        'media',
        'activity_log',
    ];

    protected $tableAliases = [
        'penempatan_pkls' => 'penempatan_p_k_l_s',
        'mutasi_pkls' => 'mutasi_p_k_l_s',
    ];

    public function index()
    {
        return Inertia::render('Admin/Backup/Index');
    }

    public function export()
    {
        try {
            $fileName = 'backup_pkl_'.Carbon::now()->format('Y-m-d_H-i-s').'.json';
            $tables = $this->tables;

            return response()->streamDownload(function () use ($tables) {
                $out = fopen('php://output', 'w');
                $timestamp = json_encode(Carbon::now()->toDateTimeString());
                fwrite($out, "{\n  \"timestamp\": {$timestamp},\n  \"version\": \"1.0\",\n  \"tables\": {\n");

                $firstTable = true;
                foreach ($tables as $table) {
                    if (! DB::getSchemaBuilder()->hasTable($table)) {
                        continue;
                    }

                    if (! $firstTable) {
                        fwrite($out, ",\n");
                    }
                    $firstTable = false;

                    $encodedTable = json_encode($table);
                    fwrite($out, "    {$encodedTable}: [\n");

                    $firstRow = true;
                    DB::table($table)->orderBy(DB::raw('1'))->chunk(200, function ($rows) use ($out, &$firstRow) {
                        foreach ($rows as $row) {
                            if (! $firstRow) {
                                fwrite($out, ",\n");
                            }
                            $firstRow = false;
                            fwrite($out, '      '.json_encode($row));
                        }
                    });

                    fwrite($out, "\n    ]");
                }

                fwrite($out, "\n  }\n}\n");
                fclose($out);
            }, $fileName, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            Log::error('Backup Export Error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal melakukan backup data: '.$e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        try {
            $file = $request->file('file');
            $extension = strtolower((string) ($file?->getClientOriginalExtension() ?: $file?->extension()));
            if ($extension !== 'json') {
                return redirect()->back()->withErrors(['file' => 'File harus berekstensi .json.']);
            }

            $content = file_get_contents($file->getRealPath());
            $data = json_decode($content, true);

            if (! $data || ! isset($data['tables'])) {
                throw new \Exception('Format file backup tidak valid.');
            }

            $backup = [
                'timestamp' => Carbon::now()->toDateTimeString(),
                'version' => '1.0',
                'tables' => [],
            ];
            foreach ($this->tables as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $backup['tables'][$table] = DB::table($table)->get()->map(function ($row) {
                        return (array) $row;
                    })->toArray();
                }
            }
            $dir = 'backup_history';
            Storage::disk('local')->makeDirectory($dir);
            $backupFile = $dir.'/backup_before_restore_'.Carbon::now()->format('Y-m-d_H-i-s').'.json';
            Storage::disk('local')->put($backupFile, json_encode($backup, JSON_PRETTY_PRINT));

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::beginTransaction();

            foreach ($data['tables'] as $table => $rows) {
                $targetTable = $this->tableAliases[$table] ?? $table;
                if (in_array($targetTable, $this->tables, true) && DB::getSchemaBuilder()->hasTable($targetTable)) {
                    DB::table($targetTable)->delete();

                    if (! empty($rows)) {
                        foreach (array_chunk($rows, 100) as $chunk) {
                            DB::table($targetTable)->insert($chunk);
                        }
                    }
                }
            }

            DB::commit();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            Cache::flush();
            if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            }

            return redirect()->back()->with('message', 'Data berhasil direstore. Cadangan otomatis tersimpan di storage/app/'.$backupFile);

        } catch (\Exception $e) {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Throwable $ignored) {
            }

            if (DB::transactionLevel() > 0) {
                try {
                    DB::rollBack();
                } catch (\Throwable $rollbackError) {
                    // Suppress if MySQL already implicitly committed
                }
            }
            Log::error('Backup Import Error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal restore data: '.$e->getMessage());
        }
    }
}
