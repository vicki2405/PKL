<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RekapAbsensiExport;
use App\Exports\RekapAbsensiPembekalanExport;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PembekalanAttendance;
use App\Models\PenempatanPKL;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class RekapAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'siswa');

        if ($tab === 'pembekalan') {
            return $this->pembekalanIndex($request);
        }

        $month = $this->normalizeIntegerFilter($request->input('month'), now()->month);
        $year = $this->normalizeIntegerFilter($request->input('year'), now()->year);
        $kelasId = $this->normalizeIntegerFilter($request->input('kelas_id'));
        $pembimbingId = $this->normalizeIntegerFilter($request->input('pembimbing_id'));
        $search = trim((string) $request->input('search', ''));

        $attendanceQuery = DB::table('monitoring_details')
            ->join('monitorings', 'monitorings.id', '=', 'monitoring_details.monitoring_id')
            ->selectRaw('
                monitoring_details.siswa_id,
                COALESCE(SUM(monitoring_details.hadir), 0) as hadir_sum,
                COALESCE(SUM(monitoring_details.izin), 0) as izin_sum,
                COALESCE(SUM(monitoring_details.sakit), 0) as sakit_sum,
                COALESCE(SUM(monitoring_details.alpha), 0) as alpha_sum
            ')
            ->whereMonth('monitorings.tanggal_kunjungan', $month)
            ->whereYear('monitorings.tanggal_kunjungan', $year)
            ->groupBy('monitoring_details.siswa_id');

        $query = Siswa::query()
            ->select([
                'siswas.id',
                'siswas.nama',
                'siswas.kelas_id',
                DB::raw('COALESCE(attendance.hadir_sum, 0) as hadir_sum'),
                DB::raw('COALESCE(attendance.izin_sum, 0) as izin_sum'),
                DB::raw('COALESCE(attendance.sakit_sum, 0) as sakit_sum'),
                DB::raw('COALESCE(attendance.alpha_sum, 0) as alpha_sum'),
            ])
            ->leftJoinSub($attendanceQuery, 'attendance', function ($join) {
                $join->on('attendance.siswa_id', '=', 'siswas.id');
            })
            ->leftJoin('kelas', 'kelas.id', '=', 'siswas.kelas_id')
            ->with([
                'kelas:id,nama_kelas',
                'penempatan:id,siswa_id,bengkel_id,pembimbing_id',
                'penempatan.bengkel:id,nama_bengkel',
                'penempatan.pembimbing:id,user_id,nama_guru',
                'penempatan.pembimbing.user:id,name',
            ]);

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        if ($pembimbingId) {
            $query->whereHas('penempatan', fn ($q) => $q->where('pembimbing_id', $pembimbingId));
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhereHas('kelas', function ($q) use ($search) {
                        $q->where('nama_kelas', 'like', "%{$search}%");
                    })
                    ->orWhereHas('penempatan.bengkel', function ($q) use ($search) {
                        $q->where('nama_bengkel', 'like', "%{$search}%");
                    })
                    ->orWhereHas('penempatan.pembimbing', function ($q) use ($search) {
                        $q->where('nama_guru', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($qu) use ($search) {
                                $qu->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $query->orderByRaw("CAST(NULLIF(REGEXP_REPLACE(kelas.nama_kelas, '[^0-9]', ''), '') AS UNSIGNED)");
        $query->orderBy('kelas.nama_kelas');
        $query->orderBy('siswas.nama');

        $perPage = $this->normalizeIntegerFilter($request->input('per_page'), 10);
        if (! in_array($perPage, [10, 20, 50, 100, 250, 500])) {
            $perPage = 10;
        }

        $siswas = $query->paginate($perPage)->withQueryString();

        // Transform data to include attendance totals
        $siswas->getCollection()->transform(function ($siswa) {
            $totalHadir = (int) ($siswa->hadir_sum ?? 0);
            $totalIzin = (int) ($siswa->izin_sum ?? 0);
            $totalSakit = (int) ($siswa->sakit_sum ?? 0);
            $totalAlpha = (int) ($siswa->alpha_sum ?? 0);

            $pembimbingNama = '-';
            if ($siswa->penempatan && $siswa->penempatan->pembimbing) {
                $pembimbingNama = $siswa->penempatan->pembimbing->nama_guru
                    ?: ($siswa->penempatan->pembimbing->user?->name ?? '-');
            }

            return [
                'id' => $siswa->id,
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas ? $siswa->kelas->nama_kelas : '-',
                'bengkel' => $siswa->penempatan && $siswa->penempatan->bengkel ? $siswa->penempatan->bengkel->nama_bengkel : '-',
                'pembimbing' => $pembimbingNama,
                'hadir' => (int) $totalHadir,
                'izin' => (int) $totalIzin,
                'sakit' => (int) $totalSakit,
                'alpha' => (int) $totalAlpha,
            ];
        });

        $kelasOptions = Cache::remember('options.kelas', 86400, function () {
            return Kelas::orderByNamaKelas()->get(['id', 'nama_kelas']);
        });

        $pembimbingOptions = \App\Models\Pembimbing::query()
            ->select('id', 'nama_guru')
            ->orderBy('nama_guru')
            ->get();

        return Inertia::render('Admin/RekapAbsensi/Index', [
            'siswas' => $siswas,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'month' => $month,
                'year' => $year,
                'kelas_id' => $kelasId,
                'pembimbing_id' => $pembimbingId,
            ],
            'kelasOptions' => $kelasOptions,
            'pembimbingOptions' => $pembimbingOptions,
            'activeTab' => 'siswa',
        ]);
    }

    public function pembekalanIndex(Request $request)
    {
        $month = $this->normalizeIntegerFilter($request->input('month'), now()->month);
        $year = $this->normalizeIntegerFilter($request->input('year'), now()->year);
        $kelasId = $this->normalizeIntegerFilter($request->input('kelas_id'));
        $pembimbingId = $this->normalizeIntegerFilter($request->input('pembimbing_id'));
        $search = trim((string) $request->input('search', ''));

        $attendanceQuery = PembekalanAttendance::query()
            ->selectRaw('
                siswa_id,
                COALESCE(SUM(hadir), 0) as hadir,
                COALESCE(SUM(izin), 0) as izin,
                COALESCE(SUM(sakit), 0) as sakit,
                COALESCE(SUM(alpha), 0) as alpha
            ')
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->groupBy('siswa_id');

        $query = Siswa::query()
            ->select([
                'siswas.id',
                'siswas.nama',
                'siswas.kelas_id',
                DB::raw('COALESCE(pembekalan.hadir, 0) as hadir'),
                DB::raw('COALESCE(pembekalan.izin, 0) as izin'),
                DB::raw('COALESCE(pembekalan.sakit, 0) as sakit'),
                DB::raw('COALESCE(pembekalan.alpha, 0) as alpha'),
            ])
            ->joinSub($attendanceQuery, 'pembekalan', function ($join) {
                $join->on('pembekalan.siswa_id', '=', 'siswas.id');
            }, null, 'left')
            ->with(['kelas:id,nama_kelas']);

        if ($kelasId) {
            $query->where('siswas.kelas_id', $kelasId);
        }

        if ($pembimbingId) {
            $query->whereHas('penempatan', fn($q) => $q->where('pembimbing_id', $pembimbingId));
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhereHas('kelas', fn($q) => $q->where('nama_kelas', 'like', "%{$search}%"))
                    ->orWhereHas('penempatan.bengkel', fn($q) => $q->where('nama_bengkel', 'like', "%{$search}%"))
                    ->orWhereHas('penempatan.pembimbing.user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $perPage = $this->normalizeIntegerFilter($request->input('per_page'), 10);
        if (! in_array($perPage, [10, 20, 50, 100, 250, 500])) {
            $perPage = 10;
        }

        $paginator = $query
            ->orderBy('nama')
            ->paginate($perPage)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($siswa) {
            return [
                'id' => $siswa->id,
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas ? $siswa->kelas->nama_kelas : '-',
                'hadir' => (int) ($siswa->hadir ?? 0),
                'izin' => (int) ($siswa->izin ?? 0),
                'sakit' => (int) ($siswa->sakit ?? 0),
                'alpha' => (int) ($siswa->alpha ?? 0),
            ];
        });

        $kelasOptions = Cache::remember('options.kelas', 86400, function () {
            return Kelas::orderByNamaKelas()->get(['id', 'nama_kelas']);
        });

        $pembimbingOptions = \App\Models\Pembimbing::query()
            ->select('id', 'nama_guru')
            ->orderBy('nama_guru')
            ->get();

        return Inertia::render('Admin/RekapAbsensi/Index', [
            'pembekalanData' => $paginator,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'month' => $month,
                'year' => $year,
                'kelas_id' => $kelasId,
                'pembimbing_id' => $pembimbingId,
            ],
            'kelasOptions' => $kelasOptions,
            'pembimbingOptions' => $pembimbingOptions,
            'activeTab' => 'pembekalan',
        ]);
    }

    public function pembekalanCreate(Request $request)
    {
        $tanggal = $request->input('tanggal', now()->toDateString());
        $pembimbingId = $this->normalizeIntegerFilter($request->input('pembimbing_id'));

        $pembimbingOptions = \App\Models\Pembimbing::query()
            ->select('id', 'nama_guru')
            ->orderBy('nama_guru')
            ->get();

        if (!$pembimbingId) {
            return Inertia::render('Admin/RekapAbsensi/PembekalanCreate', [
                'siswas' => [],
                'tanggal' => $tanggal,
                'pembimbingOptions' => $pembimbingOptions,
                'pembimbingId' => null,
                'existingAttendances' => (object)[],
            ]);
        }

        $query = Siswa::query()
            ->with(['kelas:id,nama_kelas', 'penempatan.bengkel:id,nama_bengkel'])
            ->whereHas('penempatan', fn($q) => $q->where('pembimbing_id', $pembimbingId))
            ->orderBy(
                \App\Models\Bengkel::select('nama_bengkel')
                    ->join('penempatan_p_k_l_s', 'bengkels.id', '=', 'penempatan_p_k_l_s.bengkel_id')
                    ->whereColumn('penempatan_p_k_l_s.siswa_id', 'siswas.id')
                    ->orderBy('penempatan_p_k_l_s.id')
                    ->limit(1)
            )
            ->orderBy('nama');

        $siswaCollection = $query->get();
        $siswas = $siswaCollection->map(function ($siswa) {
            return [
                'id' => $siswa->id,
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas?->nama_kelas ?? '-',
                'bengkel' => $siswa->penempatan?->bengkel?->nama_bengkel ?? '-',
            ];
        });

        $siswaIds = $siswaCollection->pluck('id');
        $existingAttendances = PembekalanAttendance::query()
            ->whereIn('siswa_id', $siswaIds)
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('siswa_id');

        return Inertia::render('Admin/RekapAbsensi/PembekalanCreate', [
            'siswas' => $siswas,
            'tanggal' => $tanggal,
            'pembimbingOptions' => $pembimbingOptions,
            'pembimbingId' => $pembimbingId,
            'existingAttendances' => $existingAttendances,
        ]);
    }

    public function pembekalanStore(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.siswa_id' => 'required|exists:siswas,id',
            'attendances.*.status' => 'required|in:hadir,izin,sakit,alpha',
        ]);

        $tanggal = $validated['tanggal'];

        foreach ($validated['attendances'] as $attendance) {
            $data = [
                'siswa_id' => $attendance['siswa_id'],
                'tanggal' => $tanggal,
                'hadir' => 0,
                'izin' => 0,
                'sakit' => 0,
                'alpha' => 0,
            ];

            $data[$attendance['status']] = 1;

            PembekalanAttendance::updateOrCreate(
                ['siswa_id' => $attendance['siswa_id'], 'tanggal' => $tanggal],
                $data,
            );
        }

        return redirect()->route('admin.rekap-absensi.index', ['tab' => 'pembekalan'])
            ->with('success', 'Absensi pembekalan berhasil disimpan.');
    }

    public function pembekalanCetak(Request $request)
    {
        $pembimbingId = $this->normalizeIntegerFilter($request->input('pembimbing_id'));

        $tanggalInput = $request->input('tanggal', now()->toDateString());
        $parsedDate = \Carbon\Carbon::parse($tanggalInput);
        $tanggalFormatted = $parsedDate->locale('id')->isoFormat('D MMMM Y');

        // Cache key: per pembimbing (or all) and date to prevent stale cross-date outputs
        // Use file store to safely cache PDF binary without DB charset issues
        $cacheKey = 'cetak_absen_pembekalan_' . ($pembimbingId ? $pembimbingId : 'all') . '_' . $parsedDate->format('Y-m-d');
        $fileCache = Cache::store('file');

        $pdfOutput = $fileCache->remember($cacheKey, now()->addMinutes(5), function () use (
            $pembimbingId, $tanggalFormatted
        ) {
            $query = PenempatanPKL::query()
                ->select('penempatan_p_k_l_s.*')
                ->join('bengkels', 'penempatan_p_k_l_s.bengkel_id', '=', 'bengkels.id')
                ->join('siswas', 'penempatan_p_k_l_s.siswa_id', '=', 'siswas.id')
                ->leftJoin('pembimbings', 'penempatan_p_k_l_s.pembimbing_id', '=', 'pembimbings.id')
                ->with([
                    'siswa:id,nama,kelas_id',
                    'siswa.kelas:id,nama_kelas',
                    'bengkel:id,nama_bengkel',
                    'pembimbing:id,nama_guru',
                ])
                ->whereHas('siswa');

            if ($pembimbingId) {
                $query->where('penempatan_p_k_l_s.pembimbing_id', $pembimbingId);
            }

            $placements = $query
                ->orderByRaw('CASE WHEN pembimbings.nama_guru IS NULL THEN 1 ELSE 0 END')
                ->orderBy('pembimbings.nama_guru')
                ->orderBy('bengkels.nama_bengkel')
                ->orderBy('siswas.nama')
                ->get();

            $grouped = $placements->groupBy(function ($p) {
                return $p->pembimbing?->nama_guru ?? 'Tanpa Pembimbing';
            });

            $pembimbingOptions = \App\Models\Pembimbing::query()
                ->select('id', 'nama_guru')
                ->orderBy('nama_guru')
                ->get();

            return Pdf::loadView('pdf.rekap_absensi_pembekalan_cetak', [
                'grouped'             => $grouped,
                'tanggal'             => $tanggalFormatted,
                'tanggalSurat'        => $tanggalFormatted,
                'pembimbingOptions'   => $pembimbingOptions,
                'selectedPembimbingId'=> $pembimbingId,
            ])->setPaper('A4', 'landscape')->output();
        });

        return response($pdfOutput, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Absen_Pembekalan_' . $tanggalInput . '.pdf"',
            'Cache-Control'       => 'no-store',
        ]);
    }

    /**
     * Clear pembekalan cetak PDF cache (call when penempatan/pembimbing data changes).
     */
    public static function clearCetakCache(?int $pembimbingId = null): void
    {
        $fileCache = Cache::store('file');
        $today = now()->format('Y-m-d');
        $fileCache->forget('cetak_absen_pembekalan_all');
        $fileCache->forget('cetak_absen_pembekalan_all_' . $today);
        if ($pembimbingId) {
            $fileCache->forget('cetak_absen_pembekalan_' . $pembimbingId);
            $fileCache->forget('cetak_absen_pembekalan_' . $pembimbingId . '_' . $today);
        }
    }

    public function exportPembekalanExcel()
    {
        $month = $this->normalizeIntegerFilter(request()->input('month'), now()->month);
        $year = $this->normalizeIntegerFilter(request()->input('year'), now()->year);
        $kelasId = $this->normalizeIntegerFilter(request()->input('kelas_id'));
        $pembimbingId = $this->normalizeIntegerFilter(request()->input('pembimbing_id'));

        $kelasName = 'Semua Kelas';
        if ($kelasId) {
            $kelas = Kelas::find($kelasId);
            if ($kelas) {
                $kelasName = $kelas->nama_kelas;
            }
        }

        $fileName = 'Rekap_Absensi_Pembekalan_'.$kelasName.'_'.$month.'_'.$year.'.xlsx';

        return Excel::download(new RekapAbsensiPembekalanExport($month, $year, $kelasId, $pembimbingId), $fileName);
    }

    public function exportPembekalanPdf()
    {
        $month = $this->normalizeIntegerFilter(request()->input('month'), now()->month);
        $year = $this->normalizeIntegerFilter(request()->input('year'), now()->year);
        $kelasId = $this->normalizeIntegerFilter(request()->input('kelas_id'));
        $pembimbingId = $this->normalizeIntegerFilter(request()->input('pembimbing_id'));

        $siswas = Siswa::whereHas('pembekalanAttendances', function ($q) use ($month, $year) {
                $q->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
            })
            ->with(['kelas:id,nama_kelas'])
            ->withSum(['pembekalanAttendances as total_hadir' => fn($q) => $q->whereMonth('tanggal', $month)->whereYear('tanggal', $year)], 'hadir')
            ->withSum(['pembekalanAttendances as total_izin' => fn($q) => $q->whereMonth('tanggal', $month)->whereYear('tanggal', $year)], 'izin')
            ->withSum(['pembekalanAttendances as total_sakit' => fn($q) => $q->whereMonth('tanggal', $month)->whereYear('tanggal', $year)], 'sakit')
            ->withSum(['pembekalanAttendances as total_alpha' => fn($q) => $q->whereMonth('tanggal', $month)->whereYear('tanggal', $year)], 'alpha')
            ->when($kelasId, fn($q) => $q->where('kelas_id', $kelasId))
            ->when($pembimbingId, fn($q) => $q->whereHas('penempatan', fn($qq) => $qq->where('pembimbing_id', $pembimbingId)))
            ->orderBy('nama')
            ->get();

        $kelasName = 'Semua Kelas';
        if ($kelasId) {
            $kelas = Kelas::find($kelasId);
            if ($kelas) {
                $kelasName = $kelas->nama_kelas;
            }
        }

        $monthName = \Carbon\Carbon::create()->month($month)->locale('id')->isoFormat('MMMM');
        $tanggalSurat = \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y');

        $latestSurat = \App\Models\SuratPengantar::latest()->first();
        $suratPengantar = (object) [
            'ketua_pkl_nama' => $latestSurat ? $latestSurat->ketua_pkl_nama : '.........................',
        ];

        $ketuaPklTtdFile = Storage::disk('public')->exists('berkas/ttd2.png')
            ? Storage::disk('public')->path('berkas/ttd2.png')
            : public_path('images/ttd2.png');

        $pdf = Pdf::loadView('pdf.rekap_absensi_pembekalan', [
            'siswas' => $siswas,
            'month' => $month,
            'monthName' => $monthName,
            'year' => $year,
            'kelasName' => $kelasName,
            'tanggalSurat' => $tanggalSurat,
            'suratPengantar' => $suratPengantar,
            'ketuaPklTtdFile' => $ketuaPklTtdFile,
        ])->setPaper([0, 0, 609.4488, 935.433], 'portrait');

        return $pdf->stream('Rekap_Absensi_Pembekalan_'.$kelasName.'_'.$month.'_'.$year.'.pdf');
    }

    public function exportExcel()
    {
        $month = $this->normalizeIntegerFilter(request()->input('month'), now()->month);
        $year = $this->normalizeIntegerFilter(request()->input('year'), now()->year);
        $kelasId = $this->normalizeIntegerFilter(request()->input('kelas_id'));

        $kelasName = 'Semua Kelas';
        if ($kelasId) {
            $kelas = Kelas::find($kelasId);
            if ($kelas) {
                $kelasName = $kelas->nama_kelas;
            }
        }

        $fileName = 'Rekap_Absensi_'.$kelasName.'_'.$month.'_'.$year.'.xlsx';

        return Excel::download(new RekapAbsensiExport($month, $year, $kelasId), $fileName);
    }

    public function exportPdf()
    {
        $month = $this->normalizeIntegerFilter(request()->input('month'), now()->month);
        $year = $this->normalizeIntegerFilter(request()->input('year'), now()->year);
        $kelasId = $this->normalizeIntegerFilter(request()->input('kelas_id'));

        $query = Siswa::query()
            ->select([
                'siswas.id',
                'siswas.nama',
                'siswas.kelas_id',
            ])
            ->leftJoin('kelas', 'kelas.id', '=', 'siswas.kelas_id')
            ->with([
                'kelas:id,nama_kelas',
                'penempatan:id,siswa_id,bengkel_id,pembimbing_id',
                'penempatan.bengkel:id,nama_bengkel',
                'penempatan.pembimbing:id,user_id,nama_guru',
                'penempatan.pembimbing.user:id,name',
            ])
            ->addSelect([
                'hadir_sum' => \App\Models\MonitoringDetail::query()
                    ->selectRaw('COALESCE(SUM(hadir), 0)')
                    ->whereColumn('siswa_id', 'siswas.id')
                    ->whereHas('monitoring', fn($q) => $q
                        ->whereMonth('tanggal_kunjungan', $month)
                        ->whereYear('tanggal_kunjungan', $year)
                    ),
                'izin_sum' => \App\Models\MonitoringDetail::query()
                    ->selectRaw('COALESCE(SUM(izin), 0)')
                    ->whereColumn('siswa_id', 'siswas.id')
                    ->whereHas('monitoring', fn($q) => $q
                        ->whereMonth('tanggal_kunjungan', $month)
                        ->whereYear('tanggal_kunjungan', $year)
                    ),
                'sakit_sum' => \App\Models\MonitoringDetail::query()
                    ->selectRaw('COALESCE(SUM(sakit), 0)')
                    ->whereColumn('siswa_id', 'siswas.id')
                    ->whereHas('monitoring', fn($q) => $q
                        ->whereMonth('tanggal_kunjungan', $month)
                        ->whereYear('tanggal_kunjungan', $year)
                    ),
                'alpha_sum' => \App\Models\MonitoringDetail::query()
                    ->selectRaw('COALESCE(SUM(alpha), 0)')
                    ->whereColumn('siswa_id', 'siswas.id')
                    ->whereHas('monitoring', fn($q) => $q
                        ->whereMonth('tanggal_kunjungan', $month)
                        ->whereYear('tanggal_kunjungan', $year)
                    ),
            ])
            ->when($kelasId, fn($query) => $query->where('siswas.kelas_id', $kelasId))
            ->orderBy('kelas.nama_kelas')
            ->orderBy('siswas.nama');

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

        $siswasByKelas = $siswas->groupBy(function ($siswa) {
            return $siswa->kelas?->nama_kelas ?? 'Tanpa Kelas';
        });

        $kelasName = 'Semua Kelas';
        if ($kelasId) {
            $kelas = Kelas::find($kelasId);
            if ($kelas) {
                $kelasName = $kelas->nama_kelas;
            }
        }

        $monthName = \Carbon\Carbon::create()->month($month)->locale('id')->isoFormat('MMMM');

        $tanggalSurat = \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y');
        $latestSurat = \App\Models\SuratPengantar::latest()->first();
        $suratPengantar = (object) [
            'ketua_pkl_nama' => $latestSurat ? $latestSurat->ketua_pkl_nama : '.........................',
        ];

        $ketuaPklTtdFile = Storage::disk('public')->exists('berkas/ttd2.png')
            ? Storage::disk('public')->path('berkas/ttd2.png')
            : public_path('images/ttd2.png');

        $pdf = Pdf::loadView('pdf.rekap_absensi', [
            'siswasByKelas' => $siswasByKelas,
            'month' => $month,
            'monthName' => $monthName,
            'year' => $year,
            'kelasName' => $kelasName,
            'tanggalSurat' => $tanggalSurat,
            'suratPengantar' => $suratPengantar,
            'ketuaPklTtdFile' => $ketuaPklTtdFile,
        ])->setPaper('F4', 'portrait');

        return $pdf->stream('REKAPITULASI_ABSENSI_PKL_'.$kelasName.'_'.ucfirst($monthName).'_'.$year.'.pdf');
    }

    public function aktivasiIndex()
    {
        $aktivasis = \App\Models\PembekalanAktivasi::query()
            ->with('creator')
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'tanggal' => $a->tanggal->toDateString(),
                    'tanggal_formatted' => $a->tanggal->locale('id')->isoFormat('D MMMM Y'),
                    'is_active' => $a->is_active,
                    'created_by' => $a->creator?->name ?? '-',
                    'created_at' => $a->created_at?->locale('id')->isoFormat('D MMMM Y HH:mm'),
                ];
            });

        return Inertia::render('Admin/RekapAbsensi/PembekalanAktivasi', [
            'aktivasis' => $aktivasis,
        ]);
    }

    public function aktivasiStore(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date|after_or_equal:today',
        ]);

        $exists = \App\Models\PembekalanAktivasi::where('tanggal', $validated['tanggal'])->exists();

        if ($exists) {
            return back()->with('error', 'Tanggal ' . $validated['tanggal'] . ' sudah diaktifkan.');
        }

        \App\Models\PembekalanAktivasi::create([
            'tanggal' => $validated['tanggal'],
            'is_active' => true,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.pembekalan-aktivasi.index')
            ->with('success', 'Aktivasi pembekalan berhasil ditambahkan.');
    }

    public function aktivasiToggle(\App\Models\PembekalanAktivasi $aktivasi)
    {
        $aktivasi->update([
            'is_active' => ! $aktivasi->is_active,
        ]);

        $status = $aktivasi->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Aktivasi pembekalan berhasil {$status}.");
    }

    public function aktivasiDestroy(\App\Models\PembekalanAktivasi $aktivasi)
    {
        $aktivasi->delete();

        return redirect()->route('admin.pembekalan-aktivasi.index')
            ->with('success', 'Aktivasi pembekalan berhasil dihapus.');
    }

    private function normalizeIntegerFilter(mixed $value, ?int $default = null): ?int
    {
        if (is_array($value)) {
            $value = $value['id'] ?? $value['value'] ?? $default;
        }

        if ($value === null || $value === '') {
            return $default;
        }

        return (int) $value;
    }
}
