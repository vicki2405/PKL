<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaSudahDapatBengkelExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No', 'NISN', 'Nama Siswa', 'Kelas', 'Jurusan',
            'Kode Bengkel', 'Nama Bengkel', 'Pembimbing',
        ];
    }

    public function map($placement): array
    {
        static $no = 0;
        $no++;

        $siswa = $placement->siswa;
        $kelas = $siswa?->kelas;
        $jurusan = $kelas?->jurusan;
        $bengkel = $placement->bengkel;
        $pembimbing = $placement->pembimbing;

        return [
            $no,
            $siswa?->nisn ?? '-',
            $siswa?->nama ?? '-',
            $kelas?->nama_kelas ?? '-',
            $jurusan?->nama_jurusan ?? '-',
            $bengkel?->kode_bengkel ?? '-',
            $bengkel?->nama_bengkel ?? '-',
            $pembimbing?->nama_guru ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
