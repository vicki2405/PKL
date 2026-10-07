<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected $filters;
    private $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Siswa::with(['kelas.jurusan', 'user', 'account'])->orderBy('nama');

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('nisn', 'like', '%' . $search . '%')
                    ->orWhere('nipd', 'like', '%' . $search . '%');
            });
        }

        if (!empty($this->filters['jurusan_id'])) {
            $jurusanId = $this->filters['jurusan_id'];
            $query->whereHas('kelas', function ($q) use ($jurusanId) {
                $q->where('jurusan_id', $jurusanId);
            });
        }

        if (!empty($this->filters['kelas_id'])) {
            $query->where('kelas_id', $this->filters['kelas_id']);
        }

        if (!empty($this->filters['jenis_kelamin'])) {
            $query->where('jenis_kelamin', $this->filters['jenis_kelamin']);
        }

        return $query;
    }

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

    public function map($siswa): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $siswa->nipd,
            $siswa->nisn,
            $siswa->nama,
            $siswa->kelas ? $siswa->kelas->nama_kelas : '-',
            $siswa->kelas && $siswa->kelas->jurusan ? $siswa->kelas->jurusan->nama_jurusan : '-',
            $siswa->tempat_lahir,
            $siswa->tanggal_lahir ? date('Y-m-d', strtotime($siswa->tanggal_lahir)) : '',
            $siswa->jenis_kelamin,
            $siswa->agama,
            $siswa->rt,
            $siswa->rw,
            $siswa->dusun,
            $siswa->kelurahan,
            $siswa->kecamatan,
            $siswa->no_hp,
            $siswa->user ? $siswa->user->email : ($siswa->account ? $siswa->account->email : ''),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
