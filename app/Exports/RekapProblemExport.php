<?php

namespace App\Exports;

use App\Models\RekapProblem;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RekapProblemExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = RekapProblem::query()->with(['siswa', 'bengkel', 'pembimbing', 'siswa.kelas', 'admin']);

        if (! empty($this->filters['tanggal'])) {
            $query->whereDate('tanggal', $this->filters['tanggal']);
        }

        if (! empty($this->filters['kelas_id'])) {
            $query->whereHas('siswa', function ($q) {
                $q->where('kelas_id', $this->filters['kelas_id']);
            });
        }

        if (! empty($this->filters['pembimbing_id'])) {
            $query->where('pembimbing_id', $this->filters['pembimbing_id']);
        }

        if (! empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa', function ($q) use ($search) {
                    $q->where('nama', 'like', '%'.$search.'%');
                })
                    ->orWhereHas('bengkel', function ($q) use ($search) {
                        $q->where('nama_bengkel', 'like', '%'.$search.'%');
                    });
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kelas',
            'Nama Siswa',
            'Nama Bengkel',
            'Pembimbing',
            'Deskripsi Masalah',
            'Deskripsi Tindakan',
            'Sanksi',
            'Admin',
        ];
    }

    public function map($row): array
    {
        return [
            $row->tanggal,
            $row->siswa && $row->siswa->kelas ? $row->siswa->kelas->nama_kelas : '-',
            $row->siswa ? $row->siswa->nama : '-',
            $row->bengkel ? $row->bengkel->nama_bengkel : '-',
            $row->pembimbing ? $row->pembimbing->nama_guru : '-',
            $row->deskripsi_masalah,
            $row->deskripsi_tindakan,
            $row->saksi, // Using saksi column as per schema
            $row->admin ? $row->admin->name : '-',
        ];
    }
}
