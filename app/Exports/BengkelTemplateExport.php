<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BengkelTemplateExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'kode_bengkel',
            'nama_bengkel',
            'nama_pemilik',
            'dusun',
            'desa',
            'kecamatan',
            'deskripsi',
            'latitude',
            'longitude',
            'google_maps_link',
            'no_hp',
        ];
    }

    public function collection()
    {
        return new Collection([
            [
                'kode_bengkel' => 'BKL-001',
                'nama_bengkel' => 'Bengkel Maju Jaya',
                'nama_pemilik' => 'Budi Santoso',
                'dusun' => 'Dusun Krajan',
                'desa' => 'Desa Sukamaju',
                'kecamatan' => 'Kecamatan Kota',
                'deskripsi' => 'Depan Balai Desa',
                'latitude' => '-7.4478',
                'longitude' => '112.7183',
                'google_maps_link' => 'https://maps.google.com/?q=-7.4478,112.7183',
                'no_hp' => '081234567890',
            ],
            [
                'kode_bengkel' => 'BKL-002',
                'nama_bengkel' => 'Bengkel Sejahtera',
                'nama_pemilik' => 'Agus Salim',
                'dusun' => 'Dusun Wetan',
                'desa' => 'Desa Karang',
                'kecamatan' => 'Kecamatan Barat',
                'deskripsi' => 'Samping Indomaret',
                'latitude' => '-7.4500',
                'longitude' => '112.7200',
                'google_maps_link' => '',
                'no_hp' => '085678901234',
            ],
        ]);
    }
}
