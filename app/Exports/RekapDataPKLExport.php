<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RekapDataPKLExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @var array<int, array<string, mixed>>|Collection<int, array<string, mixed>>
     */
    private array|Collection $data;

    /**
     * @param array<int, array<string, mixed>>|Collection<int, array<string, mixed>> $data
     */
    public function __construct(array|Collection $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $rows = [];
        foreach ($this->data as $pembimbing) {
            $details = $pembimbing['monitoring_detail'] ?? [];
            if (empty($details)) {
                $rows[] = [
                    'nama_pembimbing' => $pembimbing['nama_pembimbing'],
                    'bengkel_nama' => '-',
                    'desa' => '-',
                    'kecamatan' => '-',
                    'tanggal_ditambahkan' => '-',
                    'jumlah_siswa' => $pembimbing['jumlah_siswa'],
                    'monitoring' => $pembimbing['monitoring'] ?? 0,
                    'target' => $pembimbing['target'] ?? 0,
                ];
            } else {
                foreach ($details as $bengkel) {
                    $rows[] = [
                        'nama_pembimbing' => $pembimbing['nama_pembimbing'],
                        'bengkel_nama' => $bengkel['bengkel_nama'],
                        'desa' => $bengkel['desa'] ?? '-',
                        'kecamatan' => $bengkel['kecamatan'] ?? '-',
                        'tanggal_ditambahkan' => $bengkel['tanggal_ditambahkan'] ?? '-',
                        'jumlah_siswa' => $bengkel['siswa_count'],
                        'monitoring' => $bengkel['kunjungan_unik'],
                        'target' => $bengkel['target_kunjungan'],
                    ];
                }
            }
        }
        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Nama Pembimbing',
            'Nama Bengkel',
            'Desa',
            'Kecamatan',
            'Tanggal Ditambahkan',
            'Jumlah Siswa',
            'Monitoring',
            'Target',
        ];
    }

    public function map($row): array
    {
        return [
            $row['nama_pembimbing'],
            $row['bengkel_nama'],
            $row['desa'],
            $row['kecamatan'],
            $row['tanggal_ditambahkan'],
            $row['jumlah_siswa'],
            $row['monitoring'],
            $row['target'],
        ];
    }
}
