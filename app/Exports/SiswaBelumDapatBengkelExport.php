<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaBelumDapatBengkelExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['No', 'NISN', 'Nama Siswa', 'Kelas', 'Jurusan'];
    }

    public function map($siswa): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $siswa->nisn,
            $siswa->nama,
            $siswa->kelas ? $siswa->kelas->nama_kelas : '-',
            $siswa->kelas && $siswa->kelas->jurusan ? $siswa->kelas->jurusan->nama_jurusan : '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
