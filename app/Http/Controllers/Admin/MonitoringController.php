<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MonitoringExport;
use App\Http\Controllers\Controller;
use App\Models\Bengkel;
use App\Models\Monitoring;
use App\Models\MonitoringSetting;
use App\Models\Pembimbing;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        $query = Monitoring::query()
            ->with(['pembimbing.user', 'bengkel'])
            ->select('monitorings.*')
            ->withCount(['details as pelanggaran_count' => function ($q) {
                $q->where('is_pelanggaran', true);
            }]);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('bengkel', function ($q) use ($search) {
                    $q->where('nama_bengkel', 'like', "%{$search}%");
                })
                    ->orWhereHas('pembimbing', function ($q) use ($search) {
                        $q->where('nama_guru', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($qu) use ($search) {
                                $qu->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($request->filled('pembimbing_id')) {
            $query->where('pembimbing_id', $request->pembimbing_id);
        }

        if ($request->filled('bengkel_id')) {
            $query->where('bengkel_id', $request->bengkel_id);
        }

        if ($request->filled('session')) {
            $s = (int) $request->input('session');
            $query->where(function ($q) use ($s) {
                $q->where('monitorings.session', $s)
                  ->orWhere(function ($sub) use ($s) {
                      $sub->whereNull('monitorings.session');
                      if ($s === 1) {
                          $sub->whereRaw('DAY(tanggal_kunjungan) <= 15');
                      } else {
                          $sub->whereRaw('DAY(tanggal_kunjungan) > 15');
                      }
                  });
            });
        }

        if ($request->filled('bulan')) {
            $b = (int) $request->bulan;
            $periodSetting = MonitoringSetting::getActiveSetting();
            $period = collect($periodSetting->monthly_periods)->firstWhere('month', $b);
            if ($period && ! empty($period['start_date']) && ! empty($period['end_date'])) {
                $query->whereBetween('tanggal_kunjungan', [$period['start_date'], $period['end_date']]);
            }
        }

        if ($request->filled('monitoring_ke')) {
            $query->join(
                DB::raw('(SELECT id, ROW_NUMBER() OVER (PARTITION BY bengkel_id, pembimbing_id ORDER BY tanggal_kunjungan, id) AS monitoring_ke FROM monitorings) as sub'),
                'sub.id', '=', 'monitorings.id'
            )->where('sub.monitoring_ke', $request->monitoring_ke);
        }

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'pelanggaran') {
                $query->whereHas('details', function ($q) {
                    $q->where('is_pelanggaran', true);
                });
            } elseif ($status === 'pelanggaran_menunggu') {
                $query->where('laporan_diterima', false)
                    ->whereHas('details', function ($q) {
                        $q->where('is_pelanggaran', true);
                    });
            } elseif ($status === 'diterima') {
                $query->where('laporan_diterima', true);
            } elseif ($status === 'menunggu') {
                $query->where('laporan_diterima', false);
            }
        }

        $perPage = $request->input('per_page', 10);
        if (! in_array($perPage, [10, 20, 50, 100, 250, 500])) {
            $perPage = 10;
        }

        $monitorings = $query->orderBy('tanggal_kunjungan', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $monitorings->getCollection()->transform(function ($m) {
            $vDate = $m->tanggal_kunjungan ? $m->tanggal_kunjungan->toDateString() : now()->toDateString();
            $mInfo = MonitoringSetting::getMonthForDate($vDate);
            $m->session_display = $m->session ? (int) $m->session : (Carbon::parse($vDate)->day <= 15 ? 1 : 2);
            $m->bulan_name = $mInfo['month_name'] ?? Carbon::parse($vDate)->locale('id')->translatedFormat('F Y');
            return $m;
        });

        $totalMonitoring = Monitoring::query()->count('id');
        $totalBengkel = Monitoring::query()->distinct()->count('bengkel_id');
        $totalPembimbing = Monitoring::query()->distinct()->count('pembimbing_id');

        $pembimbingOptions = Cache::remember('options.pembimbing_monitoring', 300, function () {
            return Pembimbing::with('user:id,name')
                ->whereHas('monitorings')
                ->select('id', 'user_id', 'nama_guru')
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'label' => $p->nama_guru ?: ($p->user?->name ?? "Pembimbing #{$p->id}"),
                ]);
        });

        $bengkelOptions = Cache::remember('options.bengkel_monitoring', 300, function () {
            return Bengkel::whereHas('monitorings')
                ->select('id', 'nama_bengkel')
                ->orderBy('nama_bengkel')
                ->get()
                ->map(fn ($b) => [
                    'id' => $b->id,
                    'label' => $b->nama_bengkel,
                ]);
        });

        return Inertia::render('Admin/Monitoring/Index', [
            'monitorings' => $monitorings,
            'filters' => $request->only(['search', 'per_page', 'pembimbing_id', 'bengkel_id', 'status', 'session', 'bulan', 'monitoring_ke']),
            'totalMonitoring' => $totalMonitoring,
            'totalBengkel' => $totalBengkel,
            'totalPembimbing' => $totalPembimbing,
            'pembimbingOptions' => $pembimbingOptions,
            'bengkelOptions' => $bengkelOptions,
            'periodSetting' => MonitoringSetting::getActiveSetting(),
        ]);
    }

    public function show(Monitoring $monitoring)
    {
        $vDate = $monitoring->tanggal_kunjungan ? $monitoring->tanggal_kunjungan->toDateString() : now()->toDateString();
        $mInfo = MonitoringSetting::getMonthForDate($vDate);
        $monitoring->session_display = $monitoring->session ? (int) $monitoring->session : (Carbon::parse($vDate)->day <= 15 ? 1 : 2);
        $monitoring->bulan_name = $mInfo['month_name'] ?? Carbon::parse($vDate)->locale('id')->translatedFormat('F Y');

        return response()->json([
            'monitoring' => $monitoring->load(['pembimbing.user', 'bengkel', 'details.siswa.kelas']),
        ]);
    }

    public function terimaSemuaPelanggaran()
    {
        $updated = Monitoring::query()
            ->where('laporan_diterima', false)
            ->whereHas('details', fn ($q) => $q->where('is_pelanggaran', true))
            ->update(['laporan_diterima' => true]);

        Cache::forget('pelanggaran_pending_count');
        Cache::forget('global_notifications');

        return redirect()->back()->with('success', "Sebanyak {$updated} laporan monitoring dengan pelanggaran berhasil diterima.");
    }

    public function terimaLaporan(Monitoring $monitoring)
    {
        $monitoring->update(['laporan_diterima' => true]);

        Cache::forget('pelanggaran_pending_count');
        Cache::forget('global_notifications');

        return redirect()->back()->with('success', 'Laporan monitoring berhasil diterima.');
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['search', 'pembimbing_id', 'bengkel_id', 'status', 'session', 'bulan', 'monitoring_ke']);

        return Excel::download(new MonitoringExport($filters), 'monitoring_pkl.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['search', 'pembimbing_id', 'bengkel_id', 'status', 'session', 'bulan', 'monitoring_ke']);

        $query = Monitoring::query()
            ->with(['pembimbing.user', 'bengkel', 'details.siswa.kelas'])
            ->select('monitorings.*')
            ->join(
                DB::raw('(SELECT id, ROW_NUMBER() OVER (PARTITION BY bengkel_id, pembimbing_id ORDER BY tanggal_kunjungan, id) AS monitoring_ke FROM monitorings) as sub'),
                'sub.id', '=', 'monitorings.id'
            )
            ->selectRaw('sub.monitoring_ke')
            ->withCount(['details as pelanggaran_count' => function ($q) {
                $q->where('is_pelanggaran', true);
            }]);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('bengkel', function ($q) use ($search) {
                    $q->where('nama_bengkel', 'like', "%{$search}%");
                })
                    ->orWhereHas('pembimbing', function ($q) use ($search) {
                        $q->where('nama_guru', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($qu) use ($search) {
                                $qu->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if (! empty($filters['pembimbing_id'])) {
            $query->where('pembimbing_id', $filters['pembimbing_id']);
            $pembimbing = Pembimbing::query()->with('user')->find($filters['pembimbing_id']);
            $filters['pembimbing_nama'] = $pembimbing?->nama_guru ?? $pembimbing?->user?->name ?? '-';
        }

        if (! empty($filters['bengkel_id'])) {
            $query->where('bengkel_id', $filters['bengkel_id']);
            $bengkel = Bengkel::query()->find($filters['bengkel_id']);
            $filters['bengkel_nama'] = $bengkel?->nama_bengkel ?? '-';
        }

        if (! empty($filters['session'])) {
            $s = (int) $filters['session'];
            $query->where(function ($q) use ($s) {
                $q->where('monitorings.session', $s)
                  ->orWhere(function ($sub) use ($s) {
                      $sub->whereNull('monitorings.session');
                      if ($s === 1) {
                          $sub->whereRaw('DAY(tanggal_kunjungan) <= 15');
                      } else {
                          $sub->whereRaw('DAY(tanggal_kunjungan) > 15');
                      }
                  });
            });
            $filters['session_label'] = 'Monitoring ' . $s;
        }

        if (! empty($filters['bulan'])) {
            $b = (int) $filters['bulan'];
            $periodSetting = MonitoringSetting::getActiveSetting();
            $period = collect($periodSetting->monthly_periods)->firstWhere('month', $b);
            if ($period && ! empty($period['start_date']) && ! empty($period['end_date'])) {
                $query->whereBetween('tanggal_kunjungan', [$period['start_date'], $period['end_date']]);
                $filters['bulan_label'] = $period['month_name'] ?? ('Bulan ' . $b);
            }
        }

        if (! empty($filters['monitoring_ke'])) {
            $query->where('sub.monitoring_ke', $filters['monitoring_ke']);
        }

        if (! empty($filters['status'])) {
            $status = $filters['status'];
            if ($status === 'pelanggaran') {
                $query->whereHas('details', fn ($q) => $q->where('is_pelanggaran', true));
                $filters['status_label'] = 'Ada Pelanggaran';
            } elseif ($status === 'diterima') {
                $query->where('laporan_diterima', true);
                $filters['status_label'] = 'Laporan Diterima';
            } elseif ($status === 'menunggu') {
                $query->where('laporan_diterima', false);
                $filters['status_label'] = 'Menunggu';
            }
        }

        $data = $query->orderBy('tanggal_kunjungan', 'desc')->get();

        $data->transform(function ($m) {
            $vDate = $m->tanggal_kunjungan ? $m->tanggal_kunjungan->toDateString() : now()->toDateString();
            $mInfo = MonitoringSetting::getMonthForDate($vDate);
            $m->session_display = $m->session ? (int) $m->session : (Carbon::parse($vDate)->day <= 15 ? 1 : 2);
            $m->bulan_name = $mInfo['month_name'] ?? Carbon::parse($vDate)->locale('id')->translatedFormat('F Y');
            return $m;
        });

        $pdf = Pdf::loadView('pdf.monitoring', [
            'data' => $data,
            'filters' => $filters,
            'totalMonitoring' => $data->count(),
        ]);

        return $pdf->download('monitoring_pkl.pdf');
    }

    public function updateDetails(Request $request, Monitoring $monitoring)
    {
        $request->validate([
            'details' => 'required|array',
            'details.*.id' => 'required|exists:monitoring_details,id',
            'details.*.is_pelanggaran' => 'required|boolean',
            'details.*.catatan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $monitoring) {
            foreach ($request->details as $detailData) {
                $monitoring->details()
                    ->where('id', $detailData['id'])
                    ->update([
                        'is_pelanggaran' => $detailData['is_pelanggaran'],
                        'catatan' => $detailData['is_pelanggaran'] ? ($detailData['catatan'] ?? null) : null,
                    ]);
            }
        });

        Cache::forget('pelanggaran_pending_count');
        Cache::forget('global_notifications');

        return response()->json([
            'success' => true,
            'message' => 'Detail evaluasi monitoring berhasil diperbarui.',
        ]);
    }

    public function destroy(Monitoring $monitoring): RedirectResponse
    {
        $user = request()->user();
        if (! $user || ! $user->hasAnyRole(['admin', 'super-admin'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus monitoring.');
        }

        $pembimbingId = $monitoring->pembimbing_id;

        DB::transaction(function () use ($monitoring) {
            $monitoring->details()->delete();
            Monitoring::query()->where('id', $monitoring->id)->delete();
        });

        if ($pembimbingId) {
            Cache::forget("pembimbing:{$pembimbingId}:monitoring_progress");
        }
        Cache::forget('pelanggaran_pending_count');
        Cache::forget('global_notifications');

        return redirect()->back()->with('message', 'Data monitoring berhasil dihapus.');
    }

    public function setting(): \Inertia\Response
    {
        return Inertia::render('Admin/Monitoring/Setting', [
            'periodSetting' => MonitoringSetting::getActiveSetting(),
        ]);
    }

    public function savePeriodSetting(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $user->hasAnyRole(['admin', 'super-admin'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengubah pengaturan periode.');
        }

        $validated = $request->validate([
            'tanggal_mulai_pkl' => 'required|date',
            'tanggal_mulai_monitoring' => 'nullable|date',
            'tanggal_selesai_pkl' => 'nullable|date|after_or_equal:tanggal_mulai_pkl',
            'periode_mode' => 'required|in:calendar,cycle',
            'monthly_periods' => 'nullable|array',
            'monthly_periods.*.month' => 'required|integer',
            'monthly_periods.*.label' => 'required|string',
            'monthly_periods.*.month_name' => 'required|string',
            'monthly_periods.*.start_date' => 'required|date',
            'monthly_periods.*.end_date' => 'required|date|after_or_equal:monthly_periods.*.start_date',
            'monthly_periods.*.session_1' => 'nullable|array',
            'monthly_periods.*.session_2' => 'nullable|array',
        ]);

        $startDate = Carbon::parse($validated['tanggal_mulai_pkl'])->toDateString();
        $monitoringStartDate = ! empty($validated['tanggal_mulai_monitoring'])
            ? Carbon::parse($validated['tanggal_mulai_monitoring'])->toDateString()
            : MonitoringSetting::calculateEffectiveMonitoringStart($startDate);

        $monthlyPeriods = ! empty($validated['monthly_periods'])
            ? array_map(function ($p) {
                $s = Carbon::parse($p['start_date'])->toDateString();
                $e = Carbon::parse($p['end_date'])->toDateString();

                $s1Start = ! empty($p['session_1']['start_date']) ? Carbon::parse($p['session_1']['start_date'])->toDateString() : $s;
                $s1End = ! empty($p['session_1']['end_date']) ? Carbon::parse($p['session_1']['end_date'])->toDateString() : Carbon::parse($s)->copy()->day(min(15, (int) Carbon::parse($e)->day))->toDateString();

                $s2Start = ! empty($p['session_2']['start_date']) ? Carbon::parse($p['session_2']['start_date'])->toDateString() : Carbon::parse($s1End)->copy()->addDay()->toDateString();
                $s2End = ! empty($p['session_2']['end_date']) ? Carbon::parse($p['session_2']['end_date'])->toDateString() : $e;

                return [
                    'month' => (int) $p['month'],
                    'label' => $p['label'] ?? ('Bulan ' . $p['month']),
                    'month_name' => $p['month_name'] ?? ('Bulan ' . $p['month']),
                    'start_date' => $s,
                    'end_date' => $e,
                    'period_text' => Carbon::parse($s)->locale('id')->translatedFormat('d M Y') . ' - ' . Carbon::parse($e)->locale('id')->translatedFormat('d M Y'),
                    'target_monitoring' => 2,
                    'session_1' => [
                        'session' => 1,
                        'label' => 'Monitoring 1 (Minggu 1-2)',
                        'start_date' => $s1Start,
                        'end_date' => $s1End,
                        'period_text' => Carbon::parse($s1Start)->locale('id')->translatedFormat('d M Y') . ' - ' . Carbon::parse($s1End)->locale('id')->translatedFormat('d M Y'),
                    ],
                    'session_2' => [
                        'session' => 2,
                        'label' => 'Monitoring 2 (Minggu 3-4)',
                        'start_date' => $s2Start,
                        'end_date' => $s2End,
                        'period_text' => Carbon::parse($s2Start)->locale('id')->translatedFormat('d M Y') . ' - ' . Carbon::parse($s2End)->locale('id')->translatedFormat('d M Y'),
                    ],
                ];
            }, $validated['monthly_periods'])
            : MonitoringSetting::generatePeriodsFromStart($startDate, $validated['periode_mode'], $monitoringStartDate, $validated['tanggal_selesai_pkl'] ?? null);

        $lastPeriod = end($monthlyPeriods);
        $tanggalSelesai = ! empty($validated['tanggal_selesai_pkl'])
            ? Carbon::parse($validated['tanggal_selesai_pkl'])->toDateString()
            : ($lastPeriod ? $lastPeriod['end_date'] : null);

        MonitoringSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'tanggal_mulai_pkl' => $startDate,
                'tanggal_mulai_monitoring' => $monitoringStartDate,
                'tanggal_selesai_pkl' => $tanggalSelesai,
                'periode_mode' => $validated['periode_mode'],
                'monthly_periods' => $monthlyPeriods,
            ]
        );

        Cache::forget('monitoring.period_setting');

        // Bersihkan seluruh cache progres pembimbing agar langsung ter-update
        $pembimbingIds = Pembimbing::pluck('id')->all();
        foreach ($pembimbingIds as $pId) {
            Cache::forget("pembimbing:{$pId}:monitoring_progress");
            Cache::forget("pembimbing:{$pId}:dashboard_stats");
        }

        return redirect()->back()->with('message', 'Pengaturan periode PKL berhasil disimpan.');
    }
}
