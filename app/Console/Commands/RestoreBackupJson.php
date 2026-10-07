<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RestoreBackupJson extends Command
{
    protected $signature = 'pkl:restore-backup {path?} {--no-prebackup}';

    protected $description = 'Restore database tables from a JSON backup (BackupController format)';

    public function handle(): int
    {
        $backupPath = $this->resolveBackupPath($this->argument('path'));
        if (! $backupPath) {
            $this->error('Backup file not found.');
            return self::FAILURE;
        }

        $json = file_get_contents($backupPath);
        if ($json === false) {
            $this->error('Failed to read backup file: '.$backupPath);
            return self::FAILURE;
        }

        $data = json_decode($json, true);
        if (! is_array($data) || ! isset($data['tables']) || ! is_array($data['tables'])) {
            $this->error('Invalid backup JSON format.');
            return self::FAILURE;
        }

        $aliases = [
            'penempatan_pkls' => 'penempatan_p_k_l_s',
            'mutasi_pkls' => 'mutasi_p_k_l_s',
        ];

        $tables = array_keys($data['tables']);

        $preBackupFile = null;
        if (! $this->option('no-prebackup')) {
            $preBackupFile = $this->createPreBackup($tables, $aliases);
        }

        $restoredTables = [];
        $fkDisabled = false;
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $fkDisabled = true;

            foreach ($data['tables'] as $table => $rows) {
                $target = $aliases[$table] ?? $table;
                if (! DB::getSchemaBuilder()->hasTable($target)) {
                    continue;
                }

                DB::table($target)->truncate();

                if (is_array($rows) && ! empty($rows)) {
                    foreach (array_chunk($rows, 100) as $chunk) {
                        DB::table($target)->insert($chunk);
                    }
                }

                $restoredTables[] = $target;
            }
        } catch (\Throwable $e) {
            if ($fkDisabled) {
                try {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                } catch (\Throwable $ignored) {
                }
            }
            $this->error('Restore failed: '.$e->getMessage());
            return self::FAILURE;
        } finally {
            if ($fkDisabled) {
                try {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                } catch (\Throwable $ignored) {
                }
            }
        }

        DB::table('users')
            ->whereIn('email', ['admin@gmail.com', 'superadmin@gmail.com'])
            ->update(['email_verified_at' => now()]);

        $this->info('Restored from: '.$backupPath);
        if ($preBackupFile) {
            $this->info('Pre-backup saved: storage/app/'.$preBackupFile);
        }
        $this->info('Restored tables: '.implode(', ', array_values(array_unique($restoredTables))));

        return self::SUCCESS;
    }

    private function resolveBackupPath(?string $input): ?string
    {
        if (is_string($input) && $input !== '') {
            if (is_file($input)) {
                return $input;
            }

            $normalized = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $input);
            $candidate = storage_path('app'.DIRECTORY_SEPARATOR.ltrim($normalized, DIRECTORY_SEPARATOR));
            if (is_file($candidate)) {
                return $candidate;
            }

            return null;
        }

        $dir = storage_path('app'.DIRECTORY_SEPARATOR.'backup_history');
        if (! is_dir($dir)) {
            return null;
        }

        $files = glob($dir.DIRECTORY_SEPARATOR.'*.json') ?: [];
        if (empty($files)) {
            return null;
        }

        usort($files, function ($a, $b) {
            return (filemtime($b) ?: 0) <=> (filemtime($a) ?: 0);
        });

        return $files[0] ?? null;
    }

    private function createPreBackup(array $tables, array $aliases): ?string
    {
        $backup = [
            'timestamp' => now()->toDateTimeString(),
            'version' => '1.0',
            'tables' => [],
        ];

        foreach ($tables as $table) {
            $target = $aliases[$table] ?? $table;
            if (! DB::getSchemaBuilder()->hasTable($target)) {
                continue;
            }
            $backup['tables'][$table] = DB::table($target)->get()->map(function ($row) {
                return (array) $row;
            })->toArray();
        }

        Storage::disk('local')->makeDirectory('backup_history');
        $file = 'backup_history/backup_before_restore_'.now()->format('Y-m-d_H-i-s').'.json';
        Storage::disk('local')->put($file, json_encode($backup, JSON_PRETTY_PRINT));

        return $file;
    }
}
