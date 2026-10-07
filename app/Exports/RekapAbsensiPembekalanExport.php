<?php

namespace App\Exports;

use App\Models\Siswa;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class RekapAbsensiPembekalanExport implements FromView, WithTitle
{
    public function __construct(
        protected int $month,
        protected int $year,
        protected ?int $kelasId = null,
        protected ?int $pembimbingId = null,
    ) {}

    public function view(): View
    {
        $siswas = Siswa::whereHas('pembekalanAttendances', function ($q) {
                $q->whereMonth('tanggal', $this->month)->whereYear('tanggal', $this->year);
            })
            ->with(['kelas:id,nama_kelas'])
            ->withSum(['pembekalanAttendances as total_hadir' => fn($q) => $q->whereMonth('tanggal', $this->month)->whereYear('tanggal', $this->year)], 'hadir')
            ->withSum(['pembekalanAttendances as total_izin' => fn($q) => $q->whereMonth('tanggal', $this->month)->whereYear('tanggal', $this->year)], 'izin')
            ->withSum(['pembekalanAttendances as total_sakit' => fn($q) => $q->whereMonth('tanggal', $this->month)->whereYear('tanggal', $this->year)], 'sakit')
            ->withSum(['pembekalanAttendances as total_alpha' => fn($q) => $q->whereMonth('tanggal', $this->month)->whereYear('tanggal', $this->year)], 'alpha')
            ->when($this->kelasId, fn($q) => $q->where('kelas_id', $this->kelasId))
            ->when($this->pembimbingId, fn($q) => $q->whereHas('penempatan', fn($qq) => $qq->where('pembimbing_id', $this->pembimbingId)))
            ->orderBy('nama')
            ->get()
            ->map(function ($siswa) {
                return [
                    'nama' => $siswa->nama,
                    'kelas' => $siswa->kelas?->nama_kelas ?? '-',
                    'hadir' => (int) ($siswa->total_hadir ?? 0),
                    'izin' => (int) ($siswa->total_izin ?? 0),
                    'sakit' => (int) ($siswa->total_sakit ?? 0),
                    'alpha' => (int) ($siswa->total_alpha ?? 0),
                ];
            });

        return view('pdf.rekap_absensi_pembekalan_excel', [
            'siswas' => $siswas,
            'month' => $this->month,
            'year' => $this->year,
        ]);
    }

    public function title(): string
    {
        return 'Rekap Absensi Pembekalan';
    }
}
