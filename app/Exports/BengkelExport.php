<?php

namespace App\Exports;

use App\Models\Bengkel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BengkelExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Bengkel::latest()->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Bengkel',
            'Nama Bengkel',
            'Nama Pemilik',
            'Dusun',
            'Desa',
            'Kecamatan',
            'Deskripsi',
            'Latitude',
            'Longitude',
            'Google Maps Link',
            'No HP',
        ];
    }

    public function map($bengkel): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $bengkel->kode_bengkel,
            $bengkel->nama_bengkel,
            $bengkel->nama_pemilik,
            $bengkel->dusun,
            $bengkel->desa,
            $bengkel->kecamatan,
            $bengkel->deskripsi,
            $bengkel->latitude,
            $bengkel->longitude,
            $bengkel->google_maps_link,
            $bengkel->no_hp,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
