<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenempatanPerBengkelExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No', 'Kode Bengkel', 'Nama Bengkel', 'Dusun', 'Desa',
            'Kecamatan', 'Jumlah Siswa', 'Pembimbing',
        ];
    }

    public function map($bengkel): array
    {
        static $no = 0;
        $no++;

        $pembimbingNames = [];
        if ($bengkel->relationLoaded('penempatan_pkl') && $bengkel->penempatan_pkl) {
            $pembimbingNames = $bengkel->penempatan_pkl
                ->map(fn($p) => $p->pembimbing?->nama_guru)
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return [
            $no,
            $bengkel->kode_bengkel,
            $bengkel->nama_bengkel,
            $bengkel->dusun,
            $bengkel->desa,
            $bengkel->kecamatan,
            (int) ($bengkel->penempatan_pkl_count ?? 0),
            implode(', ', $pembimbingNames),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
