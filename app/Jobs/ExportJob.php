<?php

namespace App\Jobs;

use App\Exports\RekapAbsensiExport;
use App\Exports\RekapDataPKLExport;
use App\Exports\RekapProblemExport;
use App\Models\Monitoring;
use App\Models\Pembimbing;
use App\Models\PenempatanPKL;
use App\Models\RekapProblem;
use App\Models\Siswa;
use App\Models\Bengkel;
use App\Models\Kelas;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportJob implements ShouldQueue
{
    use Queueable;

    public string $type;
    public string $format;
    public array $filters;
    public int $userId;
    public int $timeout = 600;

    private const EXPORT_PATH = 'exports';

    public function __construct(string $type, string $format, array $filters, int $userId)
    {
        $this->type = $type;
        $this->format = $format;
        $this->filters = $filters;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(0);

        try {
            $filename = match ($this->type) {
                'rekap_absensi' => $this->exportRekapAbsensi(),
                'rekap_data_pkl' => $this->exportRekapDataPKL(),
                'rekap_problem' => $this->exportRekapProblem(),
                'monitoring' => $this->exportMonitoring(),
                'pembimbing' => $this->exportPembimbing(),
                default => throw new \InvalidArgumentException("Unknown export type: {$this->type}"),
            };

            Cache::put("export:{$this->userId}:{$this->type}", $filename, 3600);
        } catch (\Throwable $e) {
            Log::error("Export job failed: {$this->type}", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            Cache::put("export:{$this->userId}:{$this->type}:error", $e->getMessage(), 3600);
        }
    }

    private function exportRekapAbsensi(): string
    {
        $month = (int) ($this->filters['month'] ?? now()->month);
        $year = (int) ($this->filters['year'] ?? now()->year);
        $kelasId = $this->filters['kelas_id'] ?? null;

        if ($this->format === 'pdf') {
            return $this->exportRekapAbsensiPdf($month, $year, $kelasId);
        }

        $filename = "rekap_absensi_{$month}_{$year}.xlsx";
        $path = self::EXPORT_PATH . '/' . $filename;
        Excel::store(new RekapAbsensiExport($month, $year, $kelasId), $path, 'public');

        return $filename;
    }

    private function exportRekapAbsensiPdf(int $month, int $year, ?int $kelasId): string
    {
        $attendanceScope = function ($q) use ($month, $year) {
            $q->whereHas('monitoring', function ($q2) use ($month, $year) {
                $q2->whereMonth('tanggal_kunjungan', $month)
                    ->whereYear('tanggal_kunjungan', $year);
            });
        };

        $query = Siswa::query()
            ->select(['id', 'nama', 'kelas_id'])
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
            ->withSum(['monitoringDetails as alpha_sum' => $attendanceScope], 'alpha');

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        $siswas = $query->chunk(200, function ($chunk) use (&$allSiswas) {
            $allSiswas = $allSiswas ?? collect();
            $allSiswas = $allSiswas->concat($chunk);
        });

        $siswas = $allSiswas ?? collect();
        $siswas->each(function ($siswa) {
            $siswa->total_hadir = (int) ($siswa->hadir_sum ?? 0);
            $siswa->total_izin = (int) ($siswa->izin_sum ?? 0);
            $siswa->total_sakit = (int) ($siswa->sakit_sum ?? 0);
            $siswa->total_alpha = (int) ($siswa->alpha_sum ?? 0);
        });

        $kelasName = 'Semua Kelas';
        if ($kelasId) {
            $kelas = Kelas::find($kelasId);
            if ($kelas) {
                $kelasName = $kelas->nama_kelas;
            }
        }

        $monthName = Carbon::create()->month($month)->locale('id')->isoFormat('MMMM');
        $tanggalSurat = Carbon::now()->locale('id')->isoFormat('D MMMM Y');
        $latestSurat = \App\Models\SuratPengantar::latest()->first();
        $suratPengantar = (object) [
            'ketua_pkl_nama' => $latestSurat ? $latestSurat->ketua_pkl_nama : '.........................',
        ];

        $ketuaPklTtdFile = Storage::disk('public')->exists('berkas/ttd2.png')
            ? Storage::disk('public')->path('berkas/ttd2.png')
            : public_path('images/ttd2.png');

        $pdf = Pdf::loadView('pdf.rekap_absensi', [
            'siswas' => $siswas,
            'month' => $month,
            'monthName' => $monthName,
            'year' => $year,
            'kelasName' => $kelasName,
            'tanggalSurat' => $tanggalSurat,
            'suratPengantar' => $suratPengantar,
            'ketuaPklTtdFile' => $ketuaPklTtdFile,
        ])->setPaper([0, 0, 609.4488, 935.433], 'portrait');

        $filename = "rekap_absensi_{$month}_{$year}.pdf";
        $path = self::EXPORT_PATH . '/' . $filename;
        Storage::disk('public')->put($path, $pdf->output());

        return $filename;
    }

    private function exportRekapDataPKL(): string
    {
        $mode = $this->filters['mode'] ?? '6_bulan';
        $month = (int) ($this->filters['month'] ?? now()->month);
        $year = (int) ($this->filters['year'] ?? now()->year);

        $rekapController = app(\App\Http\Controllers\Admin\RekapDataPKLController::class);
        $data = $rekapController->getRekapData($mode, $month, $year);

        if ($this->format === 'pdf') {
            $date = date('d F Y');
            $pdf = Pdf::loadView('pdf.rekap_data_pkl', [
                'data' => $data,
                'date' => $date,
                'filters' => ['mode' => $mode, 'month' => $month, 'year' => $year],
            ]);
            $filename = "rekap_data_pkl_{$mode}_{$month}_{$year}.pdf";
            $path = self::EXPORT_PATH . '/' . $filename;
            Storage::disk('public')->put($path, $pdf->output());
            return $filename;
        }

        $filename = "rekap_data_pkl_{$mode}_{$month}_{$year}.xlsx";
        $path = self::EXPORT_PATH . '/' . $filename;
        Excel::store(new RekapDataPKLExport($data), $path, 'public');

        return $filename;
    }

    private function exportRekapProblem(): string
    {
        $query = RekapProblem::with(['siswa.kelas', 'bengkel', 'pembimbing', 'admin']);

        if (!empty($this->filters['tanggal'])) {
            $query->whereDate('tanggal', $this->filters['tanggal']);
        }
        if (!empty($this->filters['kelas_id'])) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $this->filters['kelas_id']));
        }
        if (!empty($this->filters['pembimbing_id'])) {
            $query->where('pembimbing_id', $this->filters['pembimbing_id']);
        }

        if ($this->format === 'pdf') {
            $data = $query->latest('tanggal')->chunk(200, function ($chunk) use (&$allData) {
                $allData = $allData ?? collect();
                $allData = $allData->concat($chunk);
            });
            $data = $allData ?? collect();

            $pdf = Pdf::loadView('pdf.rekap_problem', compact('data', 'filters'))->setPaper('a4', 'landscape');
            $filename = 'rekap_problem.pdf';
            $path = self::EXPORT_PATH . '/' . $filename;
            Storage::disk('public')->put($path, $pdf->output());
            return $filename;
        }

        $filename = 'rekap_problem.xlsx';
        $path = self::EXPORT_PATH . '/' . $filename;
        Excel::store(new RekapProblemExport($this->filters), $path, 'public');

        return $filename;
    }

    private function exportMonitoring(): string
    {
        if ($this->format === 'pdf') {
            $query = Monitoring::with(['pembimbing.user', 'bengkel', 'details.siswa.kelas'])
                ->select('monitorings.*')
                ->selectRaw('ROW_NUMBER() OVER (PARTITION BY bengkel_id, pembimbing_id ORDER BY tanggal_kunjungan, id) AS monitoring_ke');

            $data = $query->orderBy('tanggal_kunjungan', 'desc')->chunk(200, function ($chunk) use (&$allData) {
                $allData = $allData ?? collect();
                $allData = $allData->concat($chunk);
            });
            $data = $allData ?? collect();

            $pdf = Pdf::loadView('pdf.monitoring', compact('data'))->setPaper('a4', 'landscape');
            $filename = 'monitoring.pdf';
            $path = self::EXPORT_PATH . '/' . $filename;
            Storage::disk('public')->put($path, $pdf->output());
            return $filename;
        }

        $filename = 'monitoring.xlsx';
        $path = self::EXPORT_PATH . '/' . $filename;
        Excel::store(new \App\Exports\MonitoringExport($this->filters), $path, 'public');

        return $filename;
    }

    private function exportPembimbing(): string
    {
        $rows = Pembimbing::query()
            ->leftJoin('penempatan_p_k_l_s', 'pembimbings.id', '=', 'penempatan_p_k_l_s.pembimbing_id')
            ->leftJoin('jabatan_pembimbings', 'pembimbings.jabatan_pembimbing_id', '=', 'jabatan_pembimbings.id')
            ->select('pembimbings.nama_guru', 'jabatan_pembimbings.nama_jabatan')
            ->selectRaw('COUNT(penempatan_p_k_l_s.id) as siswa_count')
            ->selectRaw('COUNT(DISTINCT penempatan_p_k_l_s.bengkel_id) as bengkel_count')
            ->groupBy('pembimbings.id', 'pembimbings.nama_guru', 'jabatan_pembimbings.nama_jabatan')
            ->orderBy('pembimbings.nama_guru')
            ->get();

        if ($this->format === 'pdf') {
            $data = $rows->map(fn($row, $i) => [
                'no' => $i + 1,
                'nama_guru' => $row->nama_guru,
                'jabatan' => $row->nama_jabatan ?? '-',
                'bengkel_count' => (int) $row->bengkel_count,
                'siswa_count' => (int) $row->siswa_count,
            ]);
            $stats = [
                'pembimbing_total' => Pembimbing::count(),
                'bengkel_terisi_total' => Bengkel::whereHas('penempatan_pkl')->count(),
                'siswa_terisi_total' => PenempatanPKL::count(),
            ];
            $pdf = Pdf::loadView('pdf.input_pembimbing', compact('data', 'stats'))->setPaper('a4', 'portrait');
            $filename = 'Rekap_Pembimbing.pdf';
            $path = self::EXPORT_PATH . '/' . $filename;
            Storage::disk('public')->put($path, $pdf->output());
            return $filename;
        }

        $export = new \App\Exports\PembimbingExport($rows);
        $filename = 'Rekap_Pembimbing.xlsx';
        $path = self::EXPORT_PATH . '/' . $filename;
        Excel::store($export, $path, 'public');

        return $filename;
    }
}
