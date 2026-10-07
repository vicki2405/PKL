<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PembimbingExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly Collection $rows) {}

    public function headings(): array
    {
        return ['No', 'Nama Pembimbing', 'Jabatan', 'Jumlah Bengkel', 'Jumlah Siswa'];
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->nama_guru,
            $row->nama_jabatan ?? '-',
            (int) $row->bengkel_count,
            (int) $row->siswa_count,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
