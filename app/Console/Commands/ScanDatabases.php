<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScanDatabases extends Command
{
    protected $signature = 'db:scan-candidates';

    protected $description = 'Scan all MySQL databases for PKL tables and row counts';

    public function handle(): int
    {
        $rows = DB::select('SHOW DATABASES');
        $databases = [];
        foreach ($rows as $row) {
            $databases[] = array_values((array) $row)[0];
        }

        $exclude = [
            'information_schema',
            'mysql',
            'performance_schema',
            'sys',
            'phpmyadmin',
            'test',
        ];

        $targets = [
            'users',
            'siswas',
            'penempatan_pkls',
            'bengkels',
            'monitorings',
            'monitoring_details',
            'mutasi_pkls',
            'surat_pengantars',
            'surat_pengantar_siswa',
        ];

        $results = [];
        foreach ($databases as $db) {
            if (in_array($db, $exclude, true)) {
                continue;
            }
            $counts = [];
            $total = 0;
            foreach ($targets as $table) {
                try {
                    $countRow = DB::selectOne("SELECT COUNT(*) AS c FROM `{$db}`.`{$table}`");
                    $c = (int) ($countRow->c ?? 0);
                } catch (\Throwable $e) {
                    $c = 0;
                }
                $counts[$table] = $c;
                $total += $c;
            }
            $results[] = [
                'database' => $db,
                'total_rows' => $total,
                'users' => $counts['users'] ?? 0,
                'siswas' => $counts['siswas'] ?? 0,
                'penempatan_pkls' => $counts['penempatan_pkls'] ?? 0,
                'bengkels' => $counts['bengkels'] ?? 0,
                'monitorings' => $counts['monitorings'] ?? 0,
                'mutasi_pkls' => $counts['mutasi_pkls'] ?? 0,
            ];
        }

        usort($results, function ($a, $b) {
            return $b['total_rows'] <=> $a['total_rows'];
        });

        $this->table(
            ['Database', 'Total Rows', 'Users', 'Siswas', 'Penempatan', 'Bengkels', 'Monitorings', 'Mutasi'],
            array_map(function ($r) {
                return [
                    $r['database'],
                    $r['total_rows'],
                    $r['users'],
                    $r['siswas'],
                    $r['penempatan_pkls'],
                    $r['bengkels'],
                    $r['monitorings'],
                    $r['mutasi_pkls'],
                ];
            }, $results)
        );

        if (! empty($results)) {
            $top = $results[0];
            $this->info('Top candidate: '.$top['database'].' ('.$top['total_rows'].' rows)');
        }

        return self::SUCCESS;
    }
}
