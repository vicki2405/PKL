<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RekapDataPKLExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class RekapDataPKLController extends Controller
{
    public function index(Request $request)
    {
        $mode = $request->input('mode', '6_bulan');
        if (! in_array($mode, ['6_bulan', 'bulanan'], true)) {
            $mode = '6_bulan';
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        $data = $this->getRekapData($mode, $month, $year, false);

        return Inertia::render('Admin/RekapDataPKL/Index', [
            'data' => $data,
            'filters' => [
                'mode' => $mode,
                'month' => $month,
                'year' => $year,
            ],
        ]);
    }

    public function exportExcel(Request $request)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(0);

        $mode = $request->input('mode', '6_bulan');
        if (!in_array($mode, ['6_bulan', 'bulanan'], true)) {
            $mode = '6_bulan';
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        $data = $this->getRekapData($mode, $month, $year, true);

        return Excel::download(new RekapDataPKLExport($data), 'rekap_data_pkl_'.date('Y-m-d').'.xlsx');
    }

    public function exportPdf(Request $request)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(0);

        $mode = $request->input('mode', '6_bulan');
        if (!in_array($mode, ['6_bulan', 'bulanan'], true)) {
            $mode = '6_bulan';
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        $data = $this->getRekapData($mode, $month, $year, true);
        $date = date('d F Y');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.rekap_data_pkl', [
            'data' => $data,
            'date' => $date,
            'filters' => [
                'mode' => $mode,
                'month' => $month,
                'year' => $year,
            ],
        ]);

        return $pdf->setPaper([0, 0, 609.4488, 935.433], 'portrait')->download('rekap_data_pkl_'.date('Y-m-d').'.pdf');
    }

    public function detail(Request $request, int $pembimbing)
    {
        $mode = $request->input('mode', '6_bulan');
        if (! in_array($mode, ['6_bulan', 'bulanan'], true)) {
            $mode = '6_bulan';
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        $data = $this->getRekapData($mode, $month, $year, true, $pembimbing)->first();

        return response()->json([
            'data' => $data,
        ]);
    }

    public function getRekapData(string $mode = '6_bulan', int $month = 0, int $year = 0, bool $includeDetails = true, ?int $onlyPembimbingId = null)
    {
        $filterStart = null;
        $filterEnd = null;
        if ($mode === 'bulanan') {
            $filterStart = Carbon::create($year, $month, 1)->startOfDay();
            $filterEnd = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
        }

        $pairs = DB::table('penempatan_p_k_l_s')
            ->join('bengkels', 'penempatan_p_k_l_s.bengkel_id', '=', 'bengkels.id')
            ->whereNotNull('penempatan_p_k_l_s.pembimbing_id')
            ->when($onlyPembimbingId, fn ($query) => $query->where('penempatan_p_k_l_s.pembimbing_id', $onlyPembimbingId))
            ->select(
                'penempatan_p_k_l_s.pembimbing_id',
                'penempatan_p_k_l_s.bengkel_id',
                'bengkels.nama_bengkel',
                'bengkels.desa',
                'bengkels.kecamatan',
            )
            ->selectRaw('COUNT(penempatan_p_k_l_s.id) as siswa_count')
            ->selectRaw('MIN(penempatan_p_k_l_s.created_at) as min_penempatan_created_at')
            ->groupBy('penempatan_p_k_l_s.pembimbing_id', 'penempatan_p_k_l_s.bengkel_id', 'bengkels.nama_bengkel', 'bengkels.desa', 'bengkels.kecamatan')
            ->get();

        if ($pairs->isEmpty()) {
            return collect()->values();
        }

        $pembimbingIds = $pairs->pluck('pembimbing_id')->filter()->unique()->values()->all();
        $bengkelIds = $pairs->pluck('bengkel_id')->filter()->unique()->values()->all();

        $pembimbingMap = DB::table('pembimbings')
            ->leftJoin('jabatan_pembimbings', 'pembimbings.jabatan_pembimbing_id', '=', 'jabatan_pembimbings.id')
            ->when($onlyPembimbingId, fn ($q) => $q->where('pembimbings.id', $onlyPembimbingId))
            ->select('pembimbings.id', 'pembimbings.nama_guru', 'pembimbings.jabatan_pembimbing_id')
            ->selectRaw('jabatan_pembimbings.nama_jabatan as jabatan_nama')
            ->get()
            ->keyBy('id');

        $suratMinQuery = DB::table('penempatan_p_k_l_s')
            ->join('surat_pengantar_siswa', 'penempatan_p_k_l_s.siswa_id', '=', 'surat_pengantar_siswa.siswa_id')
            ->join('surat_pengantars', 'surat_pengantar_siswa.surat_pengantar_id', '=', 'surat_pengantars.id')
            ->whereNotNull('penempatan_p_k_l_s.pembimbing_id');

        if ($onlyPembimbingId) {
            $suratMinQuery->where('penempatan_p_k_l_s.pembimbing_id', $onlyPembimbingId)
                ->whereIn('penempatan_p_k_l_s.bengkel_id', $bengkelIds);
        }

        $suratMinMap = $suratMinQuery
            ->select('penempatan_p_k_l_s.pembimbing_id', 'penempatan_p_k_l_s.bengkel_id')
            ->selectRaw('MIN(surat_pengantars.tanggal_mulai) as min_surat_mulai')
            ->groupBy('penempatan_p_k_l_s.pembimbing_id', 'penempatan_p_k_l_s.bengkel_id')
            ->get()
            ->mapWithKeys(fn ($r) => [((int) $r->pembimbing_id).'|'.((int) $r->bengkel_id) => $r->min_surat_mulai]);

        $monitoringMinQuery = DB::table('monitorings');
        if ($onlyPembimbingId) {
            $monitoringMinQuery->where('pembimbing_id', $onlyPembimbingId)
                ->whereIn('bengkel_id', $bengkelIds);
        }

        $monitoringMinMap = $monitoringMinQuery
            ->select('pembimbing_id', 'bengkel_id')
            ->selectRaw('MIN(tanggal_kunjungan) as min_monitoring')
            ->groupBy('pembimbing_id', 'bengkel_id')
            ->get()
            ->mapWithKeys(fn ($r) => [((int) $r->pembimbing_id).'|'.((int) $r->bengkel_id) => $r->min_monitoring]);

        $detailByPembimbing = [];
        $windowStart = null;
        $windowEnd = null;

        foreach ($pairs as $pair) {
            $pId = (int) $pair->pembimbing_id;
            $bId = (int) $pair->bengkel_id;
            $key = $pId.'|'.$bId;

            $minSurat = $suratMinMap[$key] ?? null;
            $minMonitoring = $monitoringMinMap[$key] ?? null;
            $minPenempatanCreatedAt = $pair->min_penempatan_created_at ?? null;

            $startCandidates = [];
            if ($minSurat) {
                $startCandidates[] = Carbon::parse((string) $minSurat)->startOfDay();
            }
            if ($minMonitoring) {
                $startCandidates[] = Carbon::parse((string) $minMonitoring)->startOfDay();
            }
            if ($minPenempatanCreatedAt) {
                $startCandidates[] = Carbon::parse((string) $minPenempatanCreatedAt)->startOfDay();
            }

            if (empty($startCandidates)) {
                continue;
            }

            $start = null;
            foreach ($startCandidates as $candidate) {
                if (! $start || $candidate->lessThan($start)) {
                    $start = $candidate->copy();
                }
            }

            if (! $start) {
                continue;
            }

            $end = $start->copy()->addMonths(6)->subDay()->endOfDay();

            if ($windowStart === null || $start->lessThan($windowStart)) {
                $windowStart = $start->copy();
            }
            if ($windowEnd === null || $end->greaterThan($windowEnd)) {
                $windowEnd = $end->copy();
            }

            $detailByPembimbing[$pId][] = [
                'pembimbing_id' => $pId,
                'bengkel_id' => $bId,
                'bengkel_nama' => (string) $pair->nama_bengkel,
                'desa' => $pair->desa ? (string) $pair->desa : null,
                'kecamatan' => $pair->kecamatan ? (string) $pair->kecamatan : null,
                'siswa_count' => (int) $pair->siswa_count,
                'periode_mulai' => $start->toDateString(),
                'periode_selesai' => $end->toDateString(),
                'tanggal_ditambahkan' => $minPenempatanCreatedAt ? Carbon::parse((string) $minPenempatanCreatedAt)->toDateString() : null,
            ];
        }

        if (empty($detailByPembimbing)) {
            return collect()->values();
        }

        $fetchStart = $mode === 'bulanan' && $filterStart ? $filterStart : $windowStart;
        $fetchEnd = $mode === 'bulanan' && $filterEnd ? $filterEnd : $windowEnd;

        $monitoringsQuery = DB::table('monitorings')
            ->whereBetween('tanggal_kunjungan', [$fetchStart->toDateString(), $fetchEnd->toDateString()]);

        if ($onlyPembimbingId) {
            $monitoringsQuery->where('pembimbing_id', $onlyPembimbingId)
                ->whereIn('bengkel_id', $bengkelIds);
        }

        $monitorings = $monitoringsQuery
            ->select('pembimbing_id', 'bengkel_id', 'tanggal_kunjungan')
            ->get();

        $monitoringDateMap = [];
        foreach ($monitorings as $m) {
            $key = ((int) $m->pembimbing_id).'|'.((int) $m->bengkel_id);
            $monitoringDateMap[$key][] = (string) $m->tanggal_kunjungan;
        }

        $rekap = collect($detailByPembimbing)->map(function ($details, $pembimbingId) use ($mode, $filterStart, $filterEnd, $pembimbingMap, $monitoringDateMap, $includeDetails) {
            $pId = (int) $pembimbingId;
            $p = $pembimbingMap->get($pId);
            $pembimbingName = $p ? (string) $p->nama_guru : '-';
            $jabatanId = $p ? (int) ($p->jabatan_pembimbing_id ?? 0) : null;

            $detailPerBengkel = [];
            $monitoringTotal = 0;
            $targetTotal = 0;
            $jumlahSiswa = 0;

            foreach ($details as $d) {
                $bId = (int) $d['bengkel_id'];
                $key = $pId.'|'.$bId;

                $start = Carbon::parse($d['periode_mulai'])->startOfDay();
                $end = Carbon::parse($d['periode_selesai'])->endOfDay();

                $rangeStart = $start;
                $rangeEnd = $end;
                if ($mode === 'bulanan' && $filterStart && $filterEnd) {
                    $rangeStart = $start->greaterThan($filterStart) ? $start : $filterStart;
                    $rangeEnd = $end->lessThan($filterEnd) ? $end : $filterEnd;
                }

                $targetKunjungan = $mode === 'bulanan' ? 2 : 12;
                if ($mode === 'bulanan' && ! $rangeStart->lessThanOrEqualTo($rangeEnd)) {
                    $targetKunjungan = 0;
                }

                $dates = $monitoringDateMap[$key] ?? [];
                $uniqueDates = [];
                if (! empty($dates) && $rangeStart->lessThanOrEqualTo($rangeEnd)) {
                    $startStr = $rangeStart->toDateString();
                    $endStr = $rangeEnd->toDateString();
                    foreach ($dates as $dateStr) {
                        if ($dateStr >= $startStr && $dateStr <= $endStr) {
                            $uniqueDates[$dateStr] = true;
                        }
                    }
                }
                $kunjunganUnik = count($uniqueDates);

                if ($includeDetails) {
                    $detailPerBengkel[] = [
                        'bengkel_id' => $bId,
                        'bengkel_nama' => (string) $d['bengkel_nama'],
                        'desa' => $d['desa'] ?? null,
                        'kecamatan' => $d['kecamatan'] ?? null,
                        'siswa_count' => (int) $d['siswa_count'],
                        'kunjungan_unik' => $kunjunganUnik,
                        'target_kunjungan' => $targetKunjungan,
                        'periode_mulai' => (string) $d['periode_mulai'],
                        'periode_selesai' => (string) $d['periode_selesai'],
                        'tanggal_ditambahkan' => $d['tanggal_ditambahkan'] ?? null,
                    ];
                }

                // Ringkasan tidak boleh melebihi target per bengkel (cap), detail tetap asli
                $monitoringTotal += min($kunjunganUnik, $targetKunjungan);
                $targetTotal += $targetKunjungan;
                $jumlahSiswa += (int) $d['siswa_count'];
            }

            $row = [
                'pembimbing_id' => $p ? (int) $p->id : null,
                'nama_pembimbing' => $pembimbingName,
                'jabatan_pembimbing_id' => $jabatanId ?: null,
                'jumlah_bengkel' => count($details),
                'jumlah_siswa' => $jumlahSiswa,
                'monitoring' => $monitoringTotal,
                'target' => $targetTotal,
            ];

            if ($includeDetails) {
                $row['monitoring_detail'] = $detailPerBengkel;
            }

            return $row;
        })->values();

        return $rekap;
    }
}
