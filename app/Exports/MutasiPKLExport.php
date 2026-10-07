<?php

namespace App\Exports;

use App\Models\MutasiPKL;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MutasiPKLExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = MutasiPKL::with([
            'siswa.kelas',
            'bengkelAsal',
            'bengkelTujuan',
            'pembimbing',
            'admin',
        ]);

        if (isset($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        if (isset($this->filters['kelas_id']) && $this->filters['kelas_id']) {
            $kelasId = $this->filters['kelas_id'];
            $query->whereHas('siswa', function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Siswa',
            'NISN',
            'Kelas',
            'Bengkel Awal',
            'Bengkel Tujuan',
            'Alasan',
            'Status',
            'Admin',
            'Tanggal Pengajuan',
        ];
    }

    public function map($mutasi): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $mutasi->siswa->nama ?? '-',
            $mutasi->siswa->nisn ?? '-',
            $mutasi->siswa->kelas->nama_kelas ?? '-',
            $mutasi->bengkelAsal->nama_bengkel ?? '-',
            $mutasi->nama_bengkel_baru ?? ($mutasi->bengkelTujuan->nama_bengkel ?? '-'),
            $mutasi->alasan,
            strtoupper($mutasi->status),
            $mutasi->admin->name ?? '-',
            $mutasi->created_at->format('d-m-Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
