<?php

namespace App\Exports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RekapAbsensiExport implements FromCollection, WithHeadings, WithMapping
{
    protected int $month;

    protected int $year;

    protected ?int $kelas_id;

    protected int $rowNumber = 0;

    public function __construct(int $month, int $year, ?int $kelas_id = null)
    {
        $this->month = $month;
        $this->year = $year;
        $this->kelas_id = $kelas_id;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        $month = $this->month;
        $year = $this->year;

        $attendanceScope = function ($q) use ($month, $year) {
            $q->whereHas('monitoring', function ($q2) use ($month, $year) {
                $q2->whereMonth('tanggal_kunjungan', $month)
                    ->whereYear('tanggal_kunjungan', $year);
            });
        };

        $query = Siswa::query()
            ->select(['siswas.id', 'siswas.nama', 'siswas.kelas_id'])
            ->with([
                'kelas:id,nama_kelas',
                'penempatan:id,siswa_id,bengkel_id,pembimbing_id',
                'penempatan.bengkel:id,nama_bengkel',
                'penempatan.pembimbing:id,user_id,nama_guru',
                'penempatan.pembimbing.user:id,name',
            ])
            ->withSum(['monitoringDetails as hadir_sum' => $attendanceScope], 'hadir')
            ->withSum(['monitoringDetails as izin_sum' => $attendanceScope], 'izin')
            ->withSum(['monitoringDetails as sakit_sum' => $attendanceScope], 'sakit')
            ->withSum(['monitoringDetails as alpha_sum' => $attendanceScope], 'alpha')
            ->join('kelas', 'kelas.id', '=', 'siswas.kelas_id')
            ->orderBy(DB::raw("CAST(NULLIF(REGEXP_REPLACE(kelas.nama_kelas, '[^0-9]', ''), '') AS UNSIGNED)"))
            ->orderBy('kelas.nama_kelas')
            ->orderBy('siswas.nama');

        if ($this->kelas_id) {
            $query->where('siswas.kelas_id', $this->kelas_id);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Siswa',
            'Kelas',
            'Nama Bengkel',
            'Pembimbing',
            'Hadir',
            'Izin',
            'Sakit',
            'Alpha',
            'Total Absensi',
        ];
    }

    /**
     * @param  mixed  $siswa
     */
    public function map($siswa): array
    {
        $this->rowNumber++;

        $totalHadir = (int) ($siswa->hadir_sum ?? 0);
        $totalIzin = (int) ($siswa->izin_sum ?? 0);
        $totalSakit = (int) ($siswa->sakit_sum ?? 0);
        $totalAlpha = (int) ($siswa->alpha_sum ?? 0);
        $totalAbsensi = $totalHadir + $totalIzin + $totalSakit + $totalAlpha;

        return [
            $this->rowNumber,
            $siswa->nama,
            $siswa->kelas ? $siswa->kelas->nama_kelas : '-',
            $siswa->penempatan && $siswa->penempatan->bengkel ? $siswa->penempatan->bengkel->nama_bengkel : '-',
            $siswa->penempatan?->pembimbing?->nama_guru ?: ($siswa->penempatan?->pembimbing?->user?->name ?? '-'),
            (string) $totalHadir,
            (string) $totalIzin,
            (string) $totalSakit,
            (string) $totalAlpha,
            (string) $totalAbsensi,
        ];
    }
}
