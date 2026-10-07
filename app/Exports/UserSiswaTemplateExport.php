<?php

namespace App\Exports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserSiswaTemplateExport implements FromCollection, WithHeadings, WithStyles
{
    private int $rowNumber = 0;

    public function headings(): array
    {
        return [
            'No',
            'Jurusan',
            'Kelas',
            'NIPD',
            'Nama',
            'NISN',
            'Password',
        ];
    }

    public function collection(): Collection
    {
        $siswas = Siswa::with(['kelas.jurusan'])
            ->select('id', 'nama', 'nisn', 'nipd', 'kelas_id', 'siswa_user_id', 'user_account_enabled')
            ->orderBy('nama')
            ->get();

        return $siswas->map(function ($siswa) {
            $this->rowNumber++;

            return [
                $this->rowNumber,
                $siswa->kelas && $siswa->kelas->jurusan ? $siswa->kelas->jurusan->nama_jurusan : '-',
                $siswa->kelas ? $siswa->kelas->nama_kelas : '-',
                $siswa->nipd ?? '-',
                $siswa->nama,
                $siswa->nisn,
                '',
            ];
        });
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
