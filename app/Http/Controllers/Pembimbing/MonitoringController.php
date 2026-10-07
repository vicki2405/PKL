<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Bengkel;
use App\Models\Monitoring;
use App\Models\MonitoringSetting;
use App\Models\Pembimbing;
use App\Models\PenempatanPKL;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MonitoringController extends Controller
{
    public function index()
    {
        $pembimbing = $this->getCurrentPembimbing();
        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Akun Anda belum terhubung dengan data Guru Pembimbing. Silakan hubungi Admin.');
        }

        $bengkelIds = PenempatanPKL::query()
            ->where('pembimbing_id', $pembimbing->id)
            ->whereNotNull('bengkel_id')
            ->distinct()
            ->pluck('bengkel_id')
            ->values()
            ->all();

        $bengkels = empty($bengkelIds)
            ? collect()
            : Bengkel::query()
                ->whereIn('id', $bengkelIds)
                ->select(['id', 'nama_bengkel'])
                ->orderBy('nama_bengkel')
                ->get()
                ->keyBy('id');

        $setting = MonitoringSetting::getActiveSetting();
        $monthlyPeriods = $setting->monthly_periods ?: MonitoringSetting::generatePeriodsFromStart(
            (string) ($setting->tanggal_mulai_pkl ?? now()->toDateString()),
            $setting->periode_mode ?? 'calendar',
            (string) ($setting->tanggal_mulai_monitoring ?? $setting->tanggal_mulai_pkl ?? now()->toDateString()),
            $setting->tanggal_selesai_pkl ? (string) $setting->tanggal_selesai_pkl : null
        );

        $progress = Cache::remember("pembimbing:{$pembimbing->id}:monitoring_progress", 300, function () use ($pembimbing, $bengkels, $monthlyPeriods, $setting) {
            $allMonitorings = Monitoring::query()
                ->where('pembimbing_id', $pembimbing->id)
                ->select(['id', 'bengkel_id', 'session', 'tanggal_kunjungan'])
                ->get();

            $totalTarget = count($monthlyPeriods) * 2;
            $result = [];
            foreach ($bengkels as $bengkelId => $bengkel) {
                $bengkelMonitorings = $allMonitorings->where('bengkel_id', $bengkelId);
                $monthly = [];
                $total = 0;

                foreach ($monthlyPeriods as $p) {
                    $cnt = $bengkelMonitorings->filter(function ($m) use ($p) {
                        $vDate = $m->tanggal_kunjungan ? $m->tanggal_kunjungan->toDateString() : null;
                        return $vDate && $vDate >= $p['start_date'] && $vDate <= $p['end_date'];
                    })->count();

                    $s1 = $p['session_1'] ?? null;
                    $s2 = $p['session_2'] ?? null;
                    $hasS1 = false;
                    $hasS2 = false;

                    if ($s1 && ! empty($s1['start_date']) && ! empty($s1['end_date'])) {
                        $hasS1 = $bengkelMonitorings->filter(function ($m) use ($s1, $p) {
                            $vDate = $m->tanggal_kunjungan ? $m->tanggal_kunjungan->toDateString() : null;
                            if (! $vDate || $vDate < $p['start_date'] || $vDate > $p['end_date']) {
                                return false;
                            }
                            if (! empty($m->session)) {
                                return (int) $m->session === 1;
                            }
                            return $vDate >= $s1['start_date'] && $vDate <= $s1['end_date'];
                        })->isNotEmpty();
                    }

                    if ($s2 && ! empty($s2['start_date']) && ! empty($s2['end_date'])) {
                        $hasS2 = $bengkelMonitorings->filter(function ($m) use ($s2, $p) {
                            $vDate = $m->tanggal_kunjungan ? $m->tanggal_kunjungan->toDateString() : null;
                            if (! $vDate || $vDate < $p['start_date'] || $vDate > $p['end_date']) {
                                return false;
                            }
                            if (! empty($m->session)) {
                                return (int) $m->session === 2;
                            }
                            return $vDate >= $s2['start_date'] && $vDate <= $s2['end_date'];
                        })->isNotEmpty();
                    }

                    $total += $cnt;
                    $monthly[] = [
                        'month' => (int) $p['month'],
                        'month_name' => $p['month_name'],
                        'period' => $p['period_text'] ?? ($p['start_date'] . ' - ' . $p['end_date']),
                        'count' => $cnt,
                        'target' => 2,
                        'is_completed' => $cnt >= 2,
                        'has_session_1' => $hasS1,
                        'has_session_2' => $hasS2,
                        'session_1' => $s1,
                        'session_2' => $s2,
                    ];
                }

                $result[] = [
                    'bengkel_id' => $bengkelId,
                    'start_date' => ! empty($setting->tanggal_mulai_pkl) ? Carbon::parse((string) $setting->tanggal_mulai_pkl)->toDateString() : ($monthlyPeriods[0]['start_date'] ?? now()->toDateString()),
                    'nama_bengkel' => $bengkel->nama_bengkel,
                    'count' => $total,
                    'target' => $totalTarget,
                    'percentage' => $totalTarget > 0 ? min(100, (int) round(($total / $totalTarget) * 100)) : 100,
                    'monthly_progress' => $monthly,
                ];
            }

            return $result;
        });

        $monitorings = Monitoring::query()
            ->where('pembimbing_id', $pembimbing->id)
            ->select(['id', 'pembimbing_id', 'bengkel_id', 'tanggal_kunjungan', 'catatan_monitoring', 'foto_kegiatan', 'laporan_diterima', 'created_at'])
            ->with([
                'bengkel:id,nama_bengkel',
                'details:id,monitoring_id,siswa_id,catatan,is_pelanggaran,hadir,izin,sakit,alpha',
                'details.siswa:id,nama,kelas_id',
                'details.siswa.kelas:id,nama_kelas',
            ])
            ->orderBy('tanggal_kunjungan', 'desc')
            ->get();

        // Urutkan monitoring untuk menentukan bulan_ke dan monitoring_ke sesuai pengaturan periode
        $bengkelMonthCounts = [];
        $sortedAsc = $monitorings->sortBy(function ($m) {
            return $m->tanggal_kunjungan ? $m->tanggal_kunjungan->timestamp : 0;
        });

        foreach ($sortedAsc as $m) {
            $dateStr = $m->tanggal_kunjungan ? $m->tanggal_kunjungan->toDateString() : now()->toDateString();
            $mInfo = MonitoringSetting::getMonthForDate($dateStr);
            $bKe = (int) $mInfo['month'];
            $key = $m->bengkel_id . '_' . $bKe;
            $bengkelMonthCounts[$key] = ($bengkelMonthCounts[$key] ?? 0) + 1;

            $m->bulan_ke = $bKe;
            $m->bulan_name = $mInfo['month_name'];
            $m->monitoring_ke = $bengkelMonthCounts[$key];
        }

        return Inertia::render('Pembimbing/Monitoring/Index', [
            'monitorings' => [
                'data' => $monitorings,
            ],
            'progress' => $progress,
        ]);
    }

    private function getBengkelStartDates(int $pembimbingId, array $bengkelIds): array
    {
        if (empty($bengkelIds)) {
            return [];
        }

        $penempatanMin = PenempatanPKL::query()
            ->where('pembimbing_id', $pembimbingId)
            ->whereIn('bengkel_id', $bengkelIds)
            ->selectRaw('bengkel_id, MIN(created_at) as min_date')
            ->groupBy('bengkel_id')
            ->pluck('min_date', 'bengkel_id')
            ->all();

        $monitoringMin = Monitoring::query()
            ->where('pembimbing_id', $pembimbingId)
            ->whereIn('bengkel_id', $bengkelIds)
            ->selectRaw('bengkel_id, MIN(tanggal_kunjungan) as min_date')
            ->groupBy('bengkel_id')
            ->pluck('min_date', 'bengkel_id')
            ->all();

        $suratMin = DB::table('penempatan_p_k_l_s as p')
            ->join('surat_pengantar_siswa as sps', 'sps.siswa_id', '=', 'p.siswa_id')
            ->join('surat_pengantars as sp', 'sp.id', '=', 'sps.surat_pengantar_id')
            ->where('p.pembimbing_id', $pembimbingId)
            ->whereIn('p.bengkel_id', $bengkelIds)
            ->selectRaw('p.bengkel_id, MIN(sp.tanggal_mulai) as min_date')
            ->groupBy('p.bengkel_id')
            ->pluck('min_date', 'p.bengkel_id')
            ->all();

        $startDates = [];
        foreach ($bengkelIds as $bengkelId) {
            $a = $suratMin[$bengkelId] ?? null;
            $b = $monitoringMin[$bengkelId] ?? null;

            $candidates = array_values(array_filter([$a, $b]));
            if (empty($candidates)) {
                $c = $penempatanMin[$bengkelId] ?? null;
                if ($c) {
                    $startDates[$bengkelId] = $c;
                }
                continue;
            }

            $startDates[$bengkelId] = min($candidates);
        }

        return $startDates;
    }

    private function makeStartDateUnionSubquery(array $startDates)
    {
        if (empty($startDates)) {
            return null;
        }

        $sub = null;
        foreach ($startDates as $bengkelId => $startDate) {
            if (! $startDate) {
                continue;
            }
            $q = DB::query()->selectRaw('? as bengkel_id, ? as start_date', [(int) $bengkelId, (string) $startDate]);
            $sub = $sub ? $sub->unionAll($q) : $q;
        }

        return $sub;
    }

    public function create()
    {
        $pembimbing = $this->getCurrentPembimbing();
        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Akun Anda belum terhubung dengan data Guru Pembimbing. Silakan hubungi Admin.');
        }

        $penempatan = PenempatanPKL::query()
            ->where('pembimbing_id', $pembimbing->id)
            ->select(['id', 'bengkel_id', 'siswa_id'])
            ->with([
                'bengkel:id,nama_bengkel',
                'siswa:id,nama',
            ])
            ->get();

        $today = now()->startOfDay();
        $currentMonthInfo = MonitoringSetting::getMonthForDate($today->toDateString());

        $bengkels = $penempatan
            ->groupBy('bengkel_id')
            ->map(function ($items) use ($pembimbing, $today, $currentMonthInfo) {
                $bengkel = $items->first()->bengkel;
                if (! $bengkel) {
                    return null;
                }

                $students = $items
                    ->pluck('siswa')
                    ->filter()
                    ->unique('id')
                    ->values()
                    ->map(function ($siswa) {
                        return [
                            'id' => $siswa->id,
                            'nama' => $siswa->nama,
                        ];
                    })
                    ->values();

                // Hitung berapa kali monitoring sudah dicatat di periode bulan ini
                $countInMonth = Monitoring::query()
                    ->where('pembimbing_id', $pembimbing->id)
                    ->where('bengkel_id', $bengkel->id)
                    ->whereBetween('tanggal_kunjungan', [$currentMonthInfo['start_date'], $currentMonthInfo['end_date']])
                    ->count();

                // Cek apakah hari ini sudah pernah input
                $alreadyInputToday = Monitoring::query()
                    ->where('pembimbing_id', $pembimbing->id)
                    ->where('bengkel_id', $bengkel->id)
                    ->where('tanggal_kunjungan', $today->toDateString())
                    ->exists();

                return [
                    'id' => $bengkel->id,
                    'nama_bengkel' => $bengkel->nama_bengkel,
                    'students' => $students,
                    'current_month' => $currentMonthInfo['month'],
                    'month_name' => $currentMonthInfo['month_name'],
                    'period_text' => $currentMonthInfo['period_text'],
                    'current_session' => $currentMonthInfo['current_session'] ?? 1,
                    'session_label' => $currentMonthInfo['session_label'] ?? 'Monitoring Ke-1 (Minggu 1-2)',
                    'session_period_text' => $currentMonthInfo['session_period_text'] ?? '',
                    'count_in_month' => $countInMonth,
                    'is_quota_full' => $countInMonth >= 2,
                    'already_input_today' => $alreadyInputToday,
                ];
            })
            ->filter()
            ->values();

        return Inertia::render('Pembimbing/Monitoring/Create', [
            'bengkels' => $bengkels,
            'currentDate' => now()->format('Y-m-d'),
            'currentDateFormatted' => now()->locale('id')->translatedFormat('l, d F Y'),
            'currentMonthInfo' => $currentMonthInfo,
            'defaultSession' => (now()->day <= 15) ? 1 : 2,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'bengkel_id' => 'required|exists:bengkels,id',
            'session' => 'required|in:1,2',
            'catatan_monitoring' => 'required|string',
            'student_notes' => 'nullable|array',
            'student_notes.*.siswa_id' => 'required|exists:siswas,id',
            'student_notes.*.catatan' => 'nullable|string',
            'student_notes.*.is_pelanggaran' => 'nullable|boolean',
            'student_notes.*.hadir' => 'nullable|integer|min:0',
            'student_notes.*.izin' => 'nullable|integer|min:0',
            'student_notes.*.sakit' => 'nullable|integer|min:0',
            'student_notes.*.alpha' => 'nullable|integer|min:0',
        ]);

        $pembimbing = $this->getCurrentPembimbing();
        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Akun Anda belum terhubung dengan data Guru Pembimbing. Silakan hubungi Admin.');
        }

        $today = now()->toDateString();
        $currentMonthInfo = MonitoringSetting::getMonthForDate($today);

        // Cek apakah target 2x monitoring di bulan ini sudah tercapai
        $countInMonth = Monitoring::query()
            ->where('pembimbing_id', $pembimbing->id)
            ->where('bengkel_id', $request->bengkel_id)
            ->whereBetween('tanggal_kunjungan', [$currentMonthInfo['start_date'], $currentMonthInfo['end_date']])
            ->count();

        if ($countInMonth >= 2) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['bengkel_id' => 'Target monitoring untuk bengkel ini pada periode ' . $currentMonthInfo['month_name'] . ' sudah terpenuhi (maksimal 2x kunjungan).']);
        }

        $existing = Monitoring::query()
            ->where('pembimbing_id', $pembimbing->id)
            ->where('bengkel_id', $request->bengkel_id)
            ->where('tanggal_kunjungan', $today)
            ->exists();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['bengkel_id' => 'Laporan monitoring untuk bengkel ini pada hari ini (' . now()->locale('id')->translatedFormat('d F Y') . ') sudah pernah dibuat.']);
        }

        $data = $request->except(['student_notes', 'tanggal_kunjungan']);
        $data['pembimbing_id'] = $pembimbing->id;
        $data['session'] = (int) $request->input('session', (now()->day <= 15 ? 1 : 2));
        $data['tanggal_kunjungan'] = $today; // Kunci otomatis tanggal hari ini dari server

        DB::transaction(function () use ($request, $pembimbing, $data) {
            $monitoring = Monitoring::create($data);

            // Save student notes
            if ($request->has('student_notes')) {
                foreach ($request->student_notes as $note) {
                    $monitoring->details()->create([
                        'siswa_id'       => $note['siswa_id'],
                        'catatan'        => $note['catatan'] ?? null,
                        'is_pelanggaran' => $note['is_pelanggaran'] ?? false,
                        'hadir'          => $note['hadir'] ?? 0,
                        'izin'           => $note['izin'] ?? 0,
                        'sakit'          => $note['sakit'] ?? 0,
                        'alpha'          => $note['alpha'] ?? 0,
                    ]);
                }
            }
        });

        Cache::forget("pembimbing:{$pembimbing->id}:monitoring_progress");
        Cache::forget("pembimbing:{$pembimbing->id}:dashboard_stats");
        Cache::forget('pelanggaran_pending_count');
        Cache::forget('global_notifications');

        return redirect()->route('pembimbing.monitoring.index')
            ->with('success', 'Laporan monitoring berhasil disimpan.');
    }

    public function edit(Monitoring $monitoring)
    {
        $pembimbing = $this->getCurrentPembimbing();
        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Akun Anda belum terhubung dengan data Guru Pembimbing. Silakan hubungi Admin.');
        }

        $monitoring = Monitoring::query()
            ->whereKey($monitoring->id)
            ->where('pembimbing_id', $pembimbing->id)
            ->select(['id', 'pembimbing_id', 'bengkel_id', 'session', 'tanggal_kunjungan', 'catatan_monitoring', 'foto_kegiatan'])
            ->with([
                'details:id,monitoring_id,siswa_id,catatan,is_pelanggaran,hadir,izin,sakit,alpha',
                'details.siswa:id,nama',
            ])
            ->firstOrFail();

        $penempatan = PenempatanPKL::query()
            ->where('pembimbing_id', $pembimbing->id)
            ->select(['id', 'bengkel_id', 'siswa_id'])
            ->with([
                'bengkel:id,nama_bengkel',
                'siswa:id,nama',
            ])
            ->get();

        $bengkels = $penempatan
            ->groupBy('bengkel_id')
            ->map(function ($items) {
                $bengkel = $items->first()->bengkel;
                if (! $bengkel) {
                    return null;
                }

                $students = $items
                    ->pluck('siswa')
                    ->filter()
                    ->unique('id')
                    ->values()
                    ->map(function ($siswa) {
                        return [
                            'id' => $siswa->id,
                            'nama' => $siswa->nama,
                        ];
                    })
                    ->values();

                return [
                    'id' => $bengkel->id,
                    'nama_bengkel' => $bengkel->nama_bengkel,
                    'students' => $students,
                ];
            })
            ->filter()
            ->values();

        return Inertia::render('Pembimbing/Monitoring/Edit', [
            'monitoring' => $monitoring,
            'bengkels' => $bengkels,
            'visitDateFormatted' => Carbon::parse($monitoring->tanggal_kunjungan)->locale('id')->translatedFormat('l, d F Y'),
        ]);
    }

    public function update(Request $request, Monitoring $monitoring)
    {
        $request->validate([
            'bengkel_id' => 'required|exists:bengkels,id',
            'catatan_monitoring' => 'required|string',
            'student_notes' => 'nullable|array',
            'student_notes.*.siswa_id' => 'required|exists:siswas,id',
            'student_notes.*.catatan' => 'nullable|string',
            'student_notes.*.is_pelanggaran' => 'nullable|boolean',
            'student_notes.*.hadir' => 'nullable|integer|min:0',
            'student_notes.*.izin' => 'nullable|integer|min:0',
            'student_notes.*.sakit' => 'nullable|integer|min:0',
            'student_notes.*.alpha' => 'nullable|integer|min:0',
        ]);

        $pembimbing = $this->getCurrentPembimbing();
        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Akun Anda belum terhubung dengan data Guru Pembimbing. Silakan hubungi Admin.');
        }

        // Ensure the monitoring belongs to this pembimbing
        if ($monitoring->pembimbing_id !== $pembimbing->id) {
            abort(403);
        }

        $duplicateCheck = Monitoring::query()
            ->where('pembimbing_id', $pembimbing->id)
            ->where('bengkel_id', $request->bengkel_id)
            ->where('tanggal_kunjungan', $monitoring->tanggal_kunjungan)
            ->where('id', '!=', $monitoring->id)
            ->exists();

        if ($duplicateCheck) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['bengkel_id' => 'Laporan monitoring untuk bengkel ini pada tanggal tersebut sudah ada.']);
        }

        // Tanggal kunjungan dan pembimbing_id tidak dapat diubah saat update
        $data = $request->except(['student_notes', '_method', 'tanggal_kunjungan', 'pembimbing_id']);

        $monitoring->update($data);

        // Update student notes and remove deleted ones
        if ($request->has('student_notes')) {
            $updatedSiswaIds = collect($request->student_notes)->pluck('siswa_id')->all();
            $monitoring->details()->whereNotIn('siswa_id', $updatedSiswaIds)->delete();

            foreach ($request->student_notes as $note) {
                $monitoring->details()->updateOrCreate(
                    ['siswa_id' => $note['siswa_id']],
                    [
                        'catatan' => $note['catatan'] ?? null,
                        'is_pelanggaran' => $note['is_pelanggaran'] ?? false,
                        'hadir' => $note['hadir'] ?? 0,
                        'izin' => $note['izin'] ?? 0,
                        'sakit' => $note['sakit'] ?? 0,
                        'alpha' => $note['alpha'] ?? 0,
                    ]
                );
            }
        }

        Cache::forget("pembimbing:{$pembimbing->id}:monitoring_progress");
        Cache::forget("pembimbing:{$pembimbing->id}:dashboard_stats");
        Cache::forget('pelanggaran_pending_count');
        Cache::forget('global_notifications');

        return redirect()->route('pembimbing.monitoring.index')
            ->with('success', 'Laporan monitoring berhasil diperbarui.');
    }

    public function destroy(Monitoring $monitoring)
    {
        $pembimbing = $this->getCurrentPembimbing();
        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Akun Anda belum terhubung dengan data Guru Pembimbing. Silakan hubungi Admin.');
        }

        if ($monitoring->pembimbing_id !== $pembimbing->id) {
            abort(403);
        }

        $monitoring->delete();

        Cache::forget("pembimbing:{$pembimbing->id}:monitoring_progress");
        Cache::forget("pembimbing:{$pembimbing->id}:dashboard_stats");
        Cache::forget('pelanggaran_pending_count');
        Cache::forget('global_notifications');

        return redirect()->route('pembimbing.monitoring.index')
            ->with('success', 'Laporan monitoring berhasil dihapus.');
    }

    private function getCurrentPembimbing(): ?Pembimbing
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        $pembimbing = Pembimbing::where('user_id', $user->id)->first();
        if (! $pembimbing) {
            $pembimbing = Pembimbing::where('nama_guru', $user->name)->first();
            if ($pembimbing && ! $pembimbing->user_id) {
                $pembimbing->update(['user_id' => $user->id]);
            }
        }

        return $pembimbing;
    }
}
