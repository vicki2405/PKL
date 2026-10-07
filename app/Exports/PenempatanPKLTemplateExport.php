<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenempatanPKLTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            [
                1, 'BKL-001', 'Bengkel Maju Jaya', 'Jl. Raya No. 1', 'Budi Santoso',
                'Ahmad', 'XII RPL 1', 'RPL',
                'Budi', 'XII TKJ 2', 'TKJ',
                '', '', '',
                '', '', '',
                '', '', '',
                '', '', '',
                '', '', '',
                '', '', '',
                '', '', '',
                '', '', '',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Bengkel',
            'Nama Bengkel',
            'Alamat',
            'Pembimbing',
            'Nama 1', 'Kelas 1', 'Jurusan 1',
            'Nama 2', 'Kelas 2', 'Jurusan 2',
            'Nama 3', 'Kelas 3', 'Jurusan 3',
            'Nama 4', 'Kelas 4', 'Jurusan 4',
            'Nama 5', 'Kelas 5', 'Jurusan 5',
            'Nama 6', 'Kelas 6', 'Jurusan 6',
            'Nama 7', 'Kelas 7', 'Jurusan 7',
            'Nama 8', 'Kelas 8', 'Jurusan 8',
            'Nama 9', 'Kelas 9', 'Jurusan 9',
            'Nama 10', 'Kelas 10', 'Jurusan 10',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
