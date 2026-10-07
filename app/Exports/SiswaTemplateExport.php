<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaTemplateExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'No',
            'NIPD',
            'NISN',
            'Nama',
            'Kelas',
            'Jurusan',
            'Tempat Lahir',
            'Tanggal Lahir',
            'JK',
            'Agama',
            'RT',
            'RW',
            'Dusun',
            'Kelurahan',
            'Kecamatan',
            'No Hp / WA',
            'Email',
        ];
    }

    public function collection(): Collection
    {
        return collect([
            [
                '1',
                '12345678',
                '0012345678',
                'Siswa Contoh',
                'X RPL 1',
                'Rekayasa Perangkat Lunak',
                'Jakarta',
                '2005-01-01',
                'L',
                'Islam',
                '001',
                '002',
                'Dusun A',
                'Kelurahan B',
                'Kecamatan C',
                '081234567890',
                'siswa@example.com',
            ],
        ]);
    }
}
