<?php

namespace App\Http\Controllers\Admin;

use App\Exports\InputPembimbingWorkbookExport;
use App\Exports\PenempatanPerBengkelExport;
use App\Exports\SiswaBelumDapatBengkelExport;
use App\Exports\SiswaSudahDapatBengkelExport;
use App\Http\Controllers\Controller;
use App\Models\Bengkel;
use App\Models\Kelas;
use App\Models\Pembimbing;
use App\Models\PenempatanPKL;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class InputDataPKLController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->input('view', 'bengkel');
        if (! in_array($view, ['bengkel', 'siswa_sudah', 'siswa_belum'], true)) {
            $view = 'bengkel';
        }

        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');
        $pembimbingFilter = $request->input('pembimbing_filter', 'all');
        if (! in_array($pembimbingFilter, ['all', 'with_pembimbing', 'without_pembimbing'], true)) {
            $pembimbingFilter = 'all';
        }

        if ($view === 'siswa_belum') {
            $query = Siswa::query()
                ->whereDoesntHave('penempatan')
                ->with(['kelas.jurusan'])
                ->select('id', 'nama', 'nisn', 'kelas_id');

            if ($request->filled('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', '%'.$search.'%')
                        ->orWhere('nisn', 'like', '%'.$search.'%');
                });
            }

            $siswas = $query->orderBy('nama')
                ->paginate($perPage)
                ->withQueryString()
                ->through(function ($siswa) {
                    return [
                        'id' => $siswa->id,
                        'nama' => $siswa->nama,
                        'nisn' => $siswa->nisn,
                        'kelas' => $siswa->kelas ? $siswa->kelas->nama_kelas : '-',
                        'jurusan' => $siswa->kelas && $siswa->kelas->jurusan ? $siswa->kelas->jurusan->nama_jurusan : '-',
                    ];
                });

            return Inertia::render('Admin/InputDataPKL/Index', [
                'siswas' => $siswas,
                'filters' => $request->all(['view', 'search', 'per_page']),
            ]);
        }

        if ($view === 'siswa_sudah') {
            $query = PenempatanPKL::query()
                ->with([
                    'siswa:id,nama,nisn,kelas_id',
                    'siswa.kelas:id,nama_kelas,jurusan_id',
                    'siswa.kelas.jurusan:id,nama_jurusan',
                    'bengkel:id,kode_bengkel,nama_bengkel',
                    'pembimbing:id,nama_guru',
                ]);

            if ($request->filled('search')) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('siswa', function ($qs) use ($search) {
                        $qs->where('nama', 'like', '%'.$search.'%')
                            ->orWhere('nisn', 'like', '%'.$search.'%');
                    })->orWhereHas('bengkel', function ($qb) use ($search) {
                        $qb->where('nama_bengkel', 'like', '%'.$search.'%')
                            ->orWhere('kode_bengkel', 'like', '%'.$search.'%');
                    })->orWhereHas('pembimbing', function ($qp) use ($search) {
                        $qp->where('nama_guru', 'like', '%'.$search.'%');
                    });
                });
            }

            $placements = $query->latest('id')
                ->paginate($perPage)
                ->withQueryString()
                ->through(function ($placement) {
                    $siswa = $placement->siswa;
                    $bengkel = $placement->bengkel;
                    $pembimbing = $placement->pembimbing;

                    return [
                        'id' => $placement->id,
                        'siswa' => $siswa ? [
                            'id' => $siswa->id,
                            'nama' => $siswa->nama,
                            'nisn' => $siswa->nisn,
                            'kelas' => $siswa->kelas ? $siswa->kelas->nama_kelas : '-',
                            'jurusan' => $siswa->kelas && $siswa->kelas->jurusan ? $siswa->kelas->jurusan->nama_jurusan : '-',
                        ] : null,
                        'bengkel' => $bengkel ? [
                            'id' => $bengkel->id,
                            'kode_bengkel' => $bengkel->kode_bengkel,
                            'nama_bengkel' => $bengkel->nama_bengkel,
                        ] : null,
                        'pembimbing' => $pembimbing ? [
                            'id' => $pembimbing->id,
                            'nama_guru' => $pembimbing->nama_guru,
                        ] : null,
                    ];
                });

            return Inertia::render('Admin/InputDataPKL/Index', [
                'placements' => $placements,
                'filters' => $request->all(['view', 'search', 'per_page']),
            ]);
        }

        $query = Bengkel::whereHas('penempatan_pkl')
            ->with(['penempatan_pkl.pembimbing'])
            ->withCount('penempatan_pkl');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_bengkel', 'like', "%{$search}%")
                    ->orWhere('kode_bengkel', 'like', "%{$search}%")
                    ->orWhereHas('penempatan_pkl.pembimbing', function ($qp) use ($search) {
                        $qp->where('nama_guru', 'like', "%{$search}%");
                    });
            });
        }

        if ($pembimbingFilter === 'with_pembimbing') {
            $query->whereHas('penempatan_pkl', fn ($q) => $q->whereNotNull('pembimbing_id'));
        } elseif ($pembimbingFilter === 'without_pembimbing') {
            $query->whereDoesntHave('penempatan_pkl', fn ($q) => $q->whereNotNull('pembimbing_id'));
        }

        $bengkels = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/InputDataPKL/Index', [
            'bengkels' => $bengkels,
            'filters' => $request->all(['view', 'search', 'per_page', 'pembimbing_filter']),
        ]);
    }

    private function sanitizeMultiPembimbingBengkels(): int
    {
        $multiBengkelIds = PenempatanPKL::query()
            ->whereNotNull('pembimbing_id')
            ->groupBy('bengkel_id')
            ->havingRaw('COUNT(DISTINCT pembimbing_id) > 1')
            ->pluck('bengkel_id')
            ->all();

        if (! empty($multiBengkelIds)) {
            PenempatanPKL::query()
                ->whereIn('bengkel_id', $multiBengkelIds)
                ->update([
                    'pembimbing_id' => null,
                    'updated_at' => now(),
                ]);
            Cache::forget('admin_dashboard_stats');
            return count($multiBengkelIds);
        }

        return 0;
    }

    public function inputPembimbingIndex(Request $request)
    {
        $this->sanitizeMultiPembimbingBengkels();

        $search = trim((string) $request->input('search', ''));
        $jabatan = trim((string) $request->input('jabatan', ''));
        $month = trim((string) $request->input('month', ''));
        $perPage = (int) $request->input('per_page', 20);
        $page = max((int) $request->input('page', 1), 1);
        $safePerPage = min(max($perPage, 1), 100);
        $currentYear = (int) now()->year;
        $endOfMonth = ($month !== '' && $month !== 'all')
            ? Carbon::create($currentYear, (int) $month, 1)->endOfMonth()
            : null;

        $subAgg = DB::table('penempatan_p_k_l_s')
            ->select('pembimbing_id')
            ->selectRaw('COUNT(*) as siswa_count')
            ->selectRaw('COUNT(DISTINCT bengkel_id) as bengkel_count')
            ->when($endOfMonth !== null, function ($q) use ($endOfMonth, $currentYear) {
                $q->whereYear('created_at', $currentYear)
                    ->where('created_at', '<=', $endOfMonth);
            })
            ->groupBy('pembimbing_id');

        $query = Pembimbing::query()
            ->leftJoin('jabatan_pembimbings', 'pembimbings.jabatan_pembimbing_id', '=', 'jabatan_pembimbings.id')
            ->leftJoinSub($subAgg, 'penempatan_agg', function ($join) {
                $join->on('pembimbings.id', '=', 'penempatan_agg.pembimbing_id');
            })
            ->select('pembimbings.id', 'pembimbings.nama_guru', 'jabatan_pembimbings.nama_jabatan')
            ->selectRaw('COALESCE(penempatan_agg.siswa_count, 0) as siswa_count')
            ->selectRaw('COALESCE(penempatan_agg.bengkel_count, 0) as bengkel_count')
            ->orderBy('pembimbings.nama_guru');

        if ($search !== '') {
            $query->where('pembimbings.nama_guru', 'like', '%'.$search.'%');
        }

        if ($jabatan !== '' && $jabatan !== 'all') {
            if ($jabatan === 'none') {
                $query->whereNull('jabatan_pembimbings.nama_jabatan');
            } else {
                $query->where('jabatan_pembimbings.nama_jabatan', $jabatan);
            }
        }

        $rows = $query->paginate($safePerPage, ['*'], 'page', $page)->withQueryString();

        if ($rows->total() > 0 && $page > $rows->lastPage()) {
            $rows = $query->paginate($safePerPage, ['*'], 'page', 1)->withQueryString();
        }

        $rows->through(function ($r) {
            return [
                'id' => (int) $r->id,
                'nama_guru' => (string) $r->nama_guru,
                'jabatan' => $r->nama_jabatan ? (string) $r->nama_jabatan : null,
                'bengkel_count' => (int) $r->bengkel_count,
                'siswa_count' => (int) $r->siswa_count,
            ];
        });

        $unassignedBengkels = Bengkel::query()
            ->whereHas('penempatan_pkl', function ($q) use ($endOfMonth, $currentYear) {
                $q->whereNull('pembimbing_id');
                if ($endOfMonth !== null) {
                    $q->whereYear('created_at', $currentYear)
                        ->where('created_at', '<=', $endOfMonth);
                }
            })
            ->withCount(['penempatan_pkl as siswa_count' => function ($q) use ($endOfMonth, $currentYear) {
                $q->whereNull('pembimbing_id');
                if ($endOfMonth !== null) {
                    $q->whereYear('created_at', $currentYear)
                        ->where('created_at', '<=', $endOfMonth);
                }
            }])
            ->with(['penempatan_pkl' => function ($q) use ($endOfMonth, $currentYear) {
                $q->whereNull('pembimbing_id');
                if ($endOfMonth !== null) {
                    $q->whereYear('created_at', $currentYear)
                        ->where('created_at', '<=', $endOfMonth);
                }
                $q->with(['siswa:id,nama,nisn,kelas_id,no_hp', 'siswa.kelas:id,nama_kelas']);
            }])
            ->orderBy('nama_bengkel')
            ->get()
            ->map(function ($b) {
                return [
                    'id' => (int) $b->id,
                    'nama_bengkel' => (string) $b->nama_bengkel,
                    'kode_bengkel' => $b->kode_bengkel ? (string) $b->kode_bengkel : null,
                    'dusun' => $b->dusun ? (string) $b->dusun : null,
                    'desa' => $b->desa ? (string) $b->desa : null,
                    'kecamatan' => $b->kecamatan ? (string) $b->kecamatan : null,
                    'google_maps_link' => $b->google_maps_link ?: ($b->latitude && $b->longitude ? "https://www.google.com/maps?q={$b->latitude},{$b->longitude}" : null),
                    'latitude' => $b->latitude,
                    'longitude' => $b->longitude,
                    'siswa_count' => (int) $b->siswa_count,
                    'siswa' => $b->penempatan_pkl->map(fn ($p) => [
                        'id' => (int) ($p->siswa?->id ?? 0),
                        'nama' => (string) ($p->siswa?->nama ?? '-'),
                        'nisn' => $p->siswa?->nisn ? (string) $p->siswa->nisn : null,
                        'kelas' => (string) ($p->siswa?->kelas?->nama_kelas ?? '-'),
                    ])->values(),
                ];
            });

        $bengkelSudahQuery = Bengkel::whereHas('penempatan_pkl', function ($q) use ($endOfMonth, $currentYear) {
            $q->whereNotNull('pembimbing_id');
            if ($endOfMonth !== null) {
                $q->whereYear('created_at', $currentYear)
                    ->where('created_at', '<=', $endOfMonth);
            }
        });

        $penempatanSudahQuery = PenempatanPKL::query()->whereNotNull('pembimbing_id');
        if ($endOfMonth !== null) {
            $penempatanSudahQuery->whereYear('created_at', '=', $currentYear)
                ->where('created_at', '<=', $endOfMonth);
        }

        $penempatanBelumQuery = PenempatanPKL::query()->whereNull('pembimbing_id');
        if ($endOfMonth !== null) {
            $penempatanBelumQuery->whereYear('created_at', '=', $currentYear)
                ->where('created_at', '<=', $endOfMonth);
        }

        $stats = [
            'pembimbing_total' => (int) Pembimbing::query()->count('*'),
            'bengkel_terisi_total' => (int) (clone $bengkelSudahQuery)->count('*'),
            'bengkel_belum_total' => (int) $unassignedBengkels->count(),
            'siswa_terisi_total' => (int) (clone $penempatanSudahQuery)->count('*'),
            'siswa_belum_total' => (int) (clone $penempatanBelumQuery)->count('*'),
        ];

        $pembimbingOptions = Pembimbing::with('jabatanPembimbing')
            ->select('id', 'nama_guru', 'jabatan_pembimbing_id')
            ->orderBy('nama_guru')
            ->get()
            ->map(function ($p) {
                $label = $p->nama_guru;
                if ($p->jabatanPembimbing) {
                    $label .= ' — ' . $p->jabatanPembimbing->nama_jabatan;
                }
                return [
                    'id' => (int) $p->id,
                    'label' => $label,
                    'nama_guru' => (string) $p->nama_guru,
                ];
            })
            ->values();

        $jabatanOptions = \App\Models\JabatanPembimbing::query()
            ->orderBy('nama_jabatan', 'asc')
            ->pluck('nama_jabatan')
            ->toArray();

        return Inertia::render('Admin/InputDataPKL/InputPembimbingIndex', [
            'rows' => $rows,
            'stats' => $stats,
            'unassignedBengkels' => $unassignedBengkels,
            'pembimbingOptions' => $pembimbingOptions,
            'filters' => [
                'search' => $search,
                'jabatan' => $jabatan,
                'month' => $month,
                'per_page' => $safePerPage,
            ],
            'jabatanOptions' => $jabatanOptions,
        ]);
    }

    public function inputPembimbingExportExcel(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $jabatan = trim((string) $request->input('jabatan', ''));
        $month = trim((string) $request->input('month', ''));
        $currentYear = (int) now()->year;
        $endOfMonth = ($month !== '' && $month !== 'all')
            ? Carbon::create($currentYear, (int) $month, 1)->endOfMonth()
            : null;

        $query = Pembimbing::query()
            ->leftJoin('jabatan_pembimbings', 'pembimbings.jabatan_pembimbing_id', '=', 'jabatan_pembimbings.id')
            ->select('pembimbings.*');

        if ($search !== '') {
            $query->where('pembimbings.nama_guru', 'like', '%'.$search.'%');
        }

        if ($jabatan !== '' && $jabatan !== 'all') {
            if ($jabatan === 'none') {
                $query->whereNull('jabatan_pembimbings.nama_jabatan');
            } else {
                $query->where('jabatan_pembimbings.nama_jabatan', $jabatan);
            }
        }

        $query->whereHas('penempatan_pkl', function ($q) use ($endOfMonth, $currentYear) {
            if ($endOfMonth !== null) {
                $q->whereYear('created_at', $currentYear)
                    ->where('created_at', '<=', $endOfMonth);
            }
        });

        $pembimbings = $query->with([
            'penempatan_pkl' => function ($q) use ($endOfMonth, $currentYear) {
                if ($endOfMonth !== null) {
                    $q->whereYear('created_at', $currentYear)
                        ->where('created_at', '<=', $endOfMonth);
                }
            },
            'penempatan_pkl.siswa:id,nama,kelas_id,no_hp',
            'penempatan_pkl.siswa.kelas:id,nama_kelas',
            'penempatan_pkl.bengkel',
        ])
        ->orderBy('pembimbings.nama_guru')
        ->get();

        $tahunPkl = (string) now()->year;
        $tahunPelajaran = now()->year.'/'.now()->addYear()->year;
        $export = new InputPembimbingWorkbookExport($pembimbings, $tahunPkl, $tahunPelajaran);

        $labelBulan = $month && $month !== 'all' ? '_Bulan_'.$month : '';
        return Excel::download($export, 'Daftar_Peserta_PKL_dan_Pembimbing_PKL_'.$tahunPkl.$labelBulan.'.xlsx');
    }

    public function inputPembimbingExportPdf(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $jabatan = trim((string) $request->input('jabatan', ''));
        $month = trim((string) $request->input('month', ''));
        $currentYear = (int) now()->year;
        $endOfMonth = ($month !== '' && $month !== 'all')
            ? Carbon::create($currentYear, (int) $month, 1)->endOfMonth()
            : null;

        $subAgg = DB::table('penempatan_p_k_l_s')
            ->select('pembimbing_id')
            ->selectRaw('COUNT(*) as siswa_count')
            ->selectRaw('COUNT(DISTINCT bengkel_id) as bengkel_count')
            ->when($endOfMonth !== null, function ($q) use ($endOfMonth, $currentYear) {
                $q->whereYear('created_at', $currentYear)
                    ->where('created_at', '<=', $endOfMonth);
            })
            ->groupBy('pembimbing_id');

        $query = Pembimbing::query()
            ->leftJoin('jabatan_pembimbings', 'pembimbings.jabatan_pembimbing_id', '=', 'jabatan_pembimbings.id')
            ->leftJoinSub($subAgg, 'penempatan_agg', function ($join) {
                $join->on('pembimbings.id', '=', 'penempatan_agg.pembimbing_id');
            })
            ->select('pembimbings.nama_guru', 'jabatan_pembimbings.nama_jabatan')
            ->selectRaw('COALESCE(penempatan_agg.siswa_count, 0) as siswa_count')
            ->selectRaw('COALESCE(penempatan_agg.bengkel_count, 0) as bengkel_count')
            ->orderBy('pembimbings.nama_guru');

        if ($search !== '') {
            $query->where('pembimbings.nama_guru', 'like', '%'.$search.'%');
        }

        if ($jabatan !== '' && $jabatan !== 'all') {
            if ($jabatan === 'none') {
                $query->whereNull('jabatan_pembimbings.nama_jabatan');
            } else {
                $query->where('jabatan_pembimbings.nama_jabatan', $jabatan);
            }
        }

        $rows = $query->get();

        $data = $rows->map(function ($row, $index) {
            return [
                'no' => $index + 1,
                'nama_guru' => $row->nama_guru,
                'jabatan' => $row->nama_jabatan ?? '-',
                'bengkel_count' => (int) $row->bengkel_count,
                'siswa_count' => (int) $row->siswa_count,
            ];
        });

        $penempatanQuery = PenempatanPKL::query();
        if ($endOfMonth !== null) {
            $penempatanQuery->whereYear('created_at', $currentYear)
                ->where('created_at', '<=', $endOfMonth);
        }
        $bengkelQuery = Bengkel::whereHas('penempatan_pkl', function ($q) use ($endOfMonth, $currentYear) {
            if ($endOfMonth !== null) {
                $q->whereYear('created_at', $currentYear)
                    ->where('created_at', '<=', $endOfMonth);
            }
        });

        $stats = [
            'pembimbing_total' => (int) Pembimbing::query()->count('*'),
            'bengkel_terisi_total' => (int) (clone $bengkelQuery)->count('*'),
            'siswa_terisi_total' => (int) (clone $penempatanQuery)->count('*'),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.input_pembimbing', [
            'data' => $data,
            'stats' => $stats,
        ])->setPaper('a4', 'portrait');

        $labelBulan = $month && $month !== 'all' ? '_Bulan_'.$month : '';
        return $pdf->download('Rekap_Pembimbing'.$labelBulan.'.pdf');
    }

    public function inputPembimbingForm()
    {
        $pembimbings = Pembimbing::with('jabatanPembimbing')
            ->select('id', 'nama_guru', 'jabatan_pembimbing_id')
            ->orderBy('nama_guru')
            ->limit(500)
            ->get()
            ->map(function ($p) {
                $label = $p->nama_guru;
                if ($p->jabatanPembimbing) {
                    $label .= ' — '.$p->jabatanPembimbing->nama_jabatan;
                }

                return [
                    'id' => $p->id,
                    'label' => $label,
                ];
            })
            ->values();

        $bengkelOptions = Bengkel::query()
            ->whereHas('penempatan_pkl')
            ->whereDoesntHave('penempatan_pkl', fn ($q) => $q->whereNotNull('pembimbing_id'))
            ->withCount('penempatan_pkl')
            ->orderBy('nama_bengkel')
            ->limit(500)
            ->get()
            ->map(function ($bengkel) {
                $labelParts = [];
                if ($bengkel->kode_bengkel) {
                    $labelParts[] = $bengkel->kode_bengkel;
                }
                $labelParts[] = $bengkel->nama_bengkel;
                $labelParts[] = '('.(int) ($bengkel->penempatan_pkl_count ?? 0).' siswa)';

                return [
                    'id' => $bengkel->id,
                    'label' => implode(' • ', $labelParts),
                    'nama_bengkel' => $bengkel->nama_bengkel,
                    'kode_bengkel' => $bengkel->kode_bengkel,
                    'dusun' => $bengkel->dusun,
                    'desa' => $bengkel->desa,
                    'kecamatan' => $bengkel->kecamatan,
                    'penempatan_pkl_count' => (int) ($bengkel->penempatan_pkl_count ?? 0),
                    'current_pembimbing_id' => null,
                    'is_mixed_pembimbing' => false,
                ];
            })
            ->values();

        $stats = [
            'total' => (int) Bengkel::whereHas('penempatan_pkl')->count(),
            'assigned' => (int) Bengkel::whereHas('penempatan_pkl')
                ->whereHas('penempatan_pkl', fn ($q) => $q->whereNotNull('pembimbing_id'))
                ->count(),
            'unassigned' => (int) Bengkel::whereHas('penempatan_pkl')
                ->whereDoesntHave('penempatan_pkl', fn ($q) => $q->whereNotNull('pembimbing_id'))
                ->count(),
        ];

        return Inertia::render('Admin/InputDataPKL/InputPembimbing', [
            'pembimbingOptions' => $pembimbings,
            'bengkelOptions' => $bengkelOptions,
            'stats' => $stats,
        ]);
    }

    public function inputPembimbingShow(Pembimbing $pembimbing)
    {
        $pembimbing->load('jabatanPembimbing');

        $penempatans = PenempatanPKL::query()
            ->where('pembimbing_id', $pembimbing->id)
            ->with([
                'siswa.kelas.jurusan',
                'siswa.mutasis' => fn ($q) => $q->where('status', 'approved')->with('bengkelAsal')->latest(),
                'bengkel' => fn ($q) => $q->select([
                    'id', 'nama_bengkel', 'kode_bengkel', 'nama_pemilik', 'no_hp',
                    'dusun', 'desa', 'kecamatan', 'google_maps_link',
                ]),
            ])
            ->get();

        $pesertaList = $penempatans->map(function ($p) {
            $siswa = $p->siswa;
            $bengkel = $p->bengkel;
            $lastMutasi = $siswa && $siswa->mutasis ? $siswa->mutasis->first() : null;

            return [
                'id' => (int) $p->id,
                'siswa_id' => (int) $p->siswa_id,
                'nama_siswa' => $siswa ? (string) $siswa->nama : '—',
                'nisn' => $siswa ? (string) $siswa->nisn : '—',
                'kelas' => $siswa && $siswa->kelas ? (string) $siswa->kelas->nama_kelas : '—',
                'jurusan' => $siswa && $siswa->kelas && $siswa->kelas->jurusan ? (string) ($siswa->kelas->jurusan->kode_jurusan ?? $siswa->kelas->jurusan->nama_jurusan) : null,
                'no_hp_siswa' => $siswa && $siswa->no_hp ? (string) $siswa->no_hp : null,
                'pernah_pindah' => $lastMutasi ? true : false,
                'bengkel_lama' => $lastMutasi && $lastMutasi->bengkelAsal ? (string) $lastMutasi->bengkelAsal->nama_bengkel : null,
                'tanggal_pindah' => $lastMutasi && $lastMutasi->tanggal_surat ? (string) $lastMutasi->tanggal_surat : null,
                'bengkel_id' => (int) $p->bengkel_id,
                'nama_bengkel' => $bengkel ? (string) $bengkel->nama_bengkel : '—',
                'kode_bengkel' => $bengkel && $bengkel->kode_bengkel ? (string) $bengkel->kode_bengkel : null,
                'nama_pemilik' => $bengkel && $bengkel->nama_pemilik ? (string) $bengkel->nama_pemilik : '—',
                'no_hp_bengkel' => $bengkel && $bengkel->no_hp ? (string) $bengkel->no_hp : null,
                'dusun' => $bengkel && $bengkel->dusun ? (string) $bengkel->dusun : '',
                'desa' => $bengkel && $bengkel->desa ? (string) $bengkel->desa : '',
                'kecamatan' => $bengkel && $bengkel->kecamatan ? (string) $bengkel->kecamatan : '',
                'alamat_lengkap' => $bengkel ? implode(', ', array_filter([$bengkel->dusun, $bengkel->desa, $bengkel->kecamatan ? 'Kec. '.$bengkel->kecamatan : null])) : '—',
                'google_maps_link' => $bengkel ? (string) $bengkel->google_maps_link : null,
            ];
        })->sortBy([
            ['nama_bengkel', 'asc'],
            ['nama_siswa',   'asc'],
        ])->values();

        $bengkels = $penempatans->groupBy('bengkel_id')->map(function ($items, $bengkelId) {
            $b = $items->first()->bengkel;

            return [
                'id' => (int) $bengkelId,
                'nama_bengkel' => $b ? (string) $b->nama_bengkel : '—',
                'kode_bengkel' => $b && $b->kode_bengkel ? (string) $b->kode_bengkel : null,
                'nama_pemilik' => $b && $b->nama_pemilik ? (string) $b->nama_pemilik : '—',
                'no_hp' => $b && $b->no_hp ? (string) $b->no_hp : null,
                'dusun' => $b && $b->dusun ? (string) $b->dusun : null,
                'desa' => $b && $b->desa ? (string) $b->desa : null,
                'kecamatan' => $b && $b->kecamatan ? (string) $b->kecamatan : null,
                'google_maps_link' => $b ? (string) $b->google_maps_link : null,
                'siswa_count' => (int) $items->count(),
            ];
        })->values();

        $areaList = $bengkels->pluck('kecamatan')
            ->filter(fn ($k) => !empty($k) && !in_array(strtolower(trim($k)), ['-', '—', 'kecamatan', 'null', 'none']))
            ->map(function ($k) {
                $clean = trim(preg_replace('/^(kecamatan|kec\.?)\s+/i', '', trim($k)));
                return $clean ? 'Kec. ' . $clean : null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $areaMonitoring = $areaList->count() > 0
            ? $areaList->join(', ')
            : '—';

        $stats = [
            'bengkel_total' => (int) $bengkels->count(),
            'siswa_total' => (int) $pesertaList->count(),
            'area_monitoring' => $areaMonitoring,
            'tanggal_update' => now()->translatedFormat('d F Y'),
        ];

        return Inertia::render('Admin/InputDataPKL/InputPembimbingShow', [
            'pembimbing' => [
                'id' => (int) $pembimbing->id,
                'nama_guru' => (string) $pembimbing->nama_guru,
                'nip' => (string) ($pembimbing->nip ?? '—'),
                'no_hp' => (string) ($pembimbing->no_hp ?? '—'),
                'jabatan' => (string) ($pembimbing->jabatanPembimbing?->nama_jabatan ?? '—'),
            ],
            'pesertaList' => $pesertaList,
            'bengkels' => $bengkels,
            'stats' => $stats,
        ]);
    }

    public function inputPembimbingDetachBengkel(Pembimbing $pembimbing, Bengkel $bengkel)
    {
        $exists = PenempatanPKL::query()
            ->where('pembimbing_id', $pembimbing->id)
            ->where('bengkel_id', $bengkel->id)
            ->exists();

        if (! $exists) {
            return redirect()->back()->with('error', 'Data tidak ditemukan atau sudah terhapus.');
        }

        $now = now();
        DB::transaction(function () use ($pembimbing, $bengkel, $now) {
            PenempatanPKL::query()
                ->where('pembimbing_id', $pembimbing->id)
                ->where('bengkel_id', $bengkel->id)
                ->update([
                    'pembimbing_id' => null,
                    'updated_at' => $now,
                ]);
        });

        Cache::forget('admin_dashboard_stats');
        Cache::forget("pembimbing:{$pembimbing->id}:dashboard_stats");
        Cache::forget("pembimbing:{$pembimbing->id}:monitoring_progress");
        RekapAbsensiController::clearCetakCache($pembimbing->id);

        return redirect()->back()->with('message', 'Bengkel berhasil dilepas dari pembimbing.');
    }

    public function setPembimbing(Request $request, Bengkel $bengkel)
    {
        $validated = $request->validate([
            'pembimbing_id' => 'nullable|exists:pembimbings,id',
        ]);

        $hasPlacement = PenempatanPKL::query()->where('bengkel_id', $bengkel->id)->exists();
        if (! $hasPlacement) {
            return redirect()->back()->with('error', 'Bengkel ini belum memiliki siswa, jadi tidak bisa diatur pembimbingnya.');
        }

        $oldPembimbingIds = PenempatanPKL::query()->where('bengkel_id', $bengkel->id)
            ->whereNotNull('pembimbing_id')
            ->distinct()
            ->pluck('pembimbing_id')
            ->all();

        $now = now();
        PenempatanPKL::query()->where('bengkel_id', $bengkel->id)->update([
            'pembimbing_id' => $validated['pembimbing_id'] ?? null,
            'updated_at' => $now,
        ]);

        Cache::forget('admin_dashboard_stats');
        foreach ($oldPembimbingIds as $id) {
            Cache::forget("pembimbing:{$id}:dashboard_stats");
            Cache::forget("pembimbing:{$id}:monitoring_progress");
            RekapAbsensiController::clearCetakCache($id);
        }
        if (! empty($validated['pembimbing_id'])) {
            Cache::forget("pembimbing:{$validated['pembimbing_id']}:dashboard_stats");
            Cache::forget("pembimbing:{$validated['pembimbing_id']}:monitoring_progress");
            RekapAbsensiController::clearCetakCache((int) $validated['pembimbing_id']);
        }

        return redirect()->back()->with('message', 'Pembimbing untuk bengkel berhasil diperbarui.');
    }

    public function bulkSetPembimbing(Request $request)
    {
        $validated = $request->validate([
            'pembimbing_id' => 'required|exists:pembimbings,id',
            'bengkel_ids' => 'required|array|min:1',
            'bengkel_ids.*' => 'integer|exists:bengkels,id',
        ]);

        $bengkelIds = collect($validated['bengkel_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $eligibleIds = Bengkel::query()
            ->whereHas('penempatan_pkl')
            ->whereIn('id', $bengkelIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($eligibleIds) !== count($bengkelIds)) {
            return redirect()->back()->with('error', 'Ada bengkel yang belum ditempati siswa, jadi tidak bisa dimasukkan.');
        }

        $alreadyAssigned = Bengkel::query()
            ->whereIn('id', $bengkelIds)
            ->whereHas('penempatan_pkl', fn ($q) => $q->whereNotNull('pembimbing_id'))
            ->get();

        if ($alreadyAssigned->isNotEmpty()) {
            $names = $alreadyAssigned->take(3)->pluck('nama_bengkel')->implode(', ');
            $more = $alreadyAssigned->count() > 3 ? ' dan '.($alreadyAssigned->count() - 3).' lainnya' : '';

            return redirect()
                ->back()
                ->with('error', 'Bengkel '.$names.$more.' sudah memiliki pembimbing. Satu bengkel hanya boleh dibimbing oleh satu pembimbing. Silakan lepas pembimbing lama terlebih dahulu atau gunakan menu ganti pembimbing.');
        }

        $oldPembimbingIds = PenempatanPKL::query()->whereIn('bengkel_id', $bengkelIds)
            ->whereNotNull('pembimbing_id')
            ->distinct()
            ->pluck('pembimbing_id')
            ->all();

        $now = now();
        DB::transaction(function () use ($validated, $bengkelIds, $now) {
            PenempatanPKL::query()->whereIn('bengkel_id', $bengkelIds)->update([
                'pembimbing_id' => $validated['pembimbing_id'],
                'updated_at' => $now,
            ]);
        });

        Cache::forget('admin_dashboard_stats');
        $affectedIds = array_unique([...$oldPembimbingIds, (int) $validated['pembimbing_id']]);
        foreach ($affectedIds as $id) {
            Cache::forget("pembimbing:{$id}:dashboard_stats");
            Cache::forget("pembimbing:{$id}:monitoring_progress");
            RekapAbsensiController::clearCetakCache((int) $id);
        }

        return redirect()
            ->route('admin.input_data_pkl.input_pembimbing')
            ->with('message', 'Pembimbing berhasil diterapkan ke '.count($bengkelIds).' bengkel.');
    }

    public function create()
    {
        $bengkels = Bengkel::query()->select(['id', 'nama_bengkel', 'kode_bengkel'])->orderBy('nama_bengkel')->limit(500)->get();
        $kelas = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $pembimbings = Pembimbing::query()->select(['id', 'nama_guru'])->orderBy('nama_guru')->limit(500)->get();

        return Inertia::render('Admin/InputDataPKL/Create', [
            'bengkelOptions' => $bengkels,
            'kelasOptions' => $kelas,
            'siswaOptions' => [],
            'pembimbingOptions' => $pembimbings,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bengkel_id' => 'required|exists:bengkels,id',
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswas,id',
            'pembimbing_id' => 'nullable|exists:pembimbings,id',
        ]);

        $siswaIds = collect($validated['siswa_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $alreadyPlacedIds = PenempatanPKL::query()
            ->whereIn('siswa_id', $siswaIds)
            ->pluck('siswa_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $existingPembimbingId = PenempatanPKL::query()
            ->where('bengkel_id', (int) $validated['bengkel_id'])
            ->whereNotNull('pembimbing_id')
            ->value('pembimbing_id');

        $pembimbingId = $validated['pembimbing_id'] ?? $existingPembimbingId ?? null;

        $rows = $siswaIds
            ->reject(fn ($id) => in_array($id, $alreadyPlacedIds, true))
            ->map(function ($siswaId) use ($validated, $pembimbingId) {
                $now = now();

                return [
                    'bengkel_id' => (int) $validated['bengkel_id'],
                    'siswa_id' => $siswaId,
                    'pembimbing_id' => $pembimbingId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->values()
            ->all();

        if (empty($rows)) {
            return redirect()->back()->with('error', 'Semua siswa yang dipilih sudah memiliki penempatan PKL.');
        }

        DB::transaction(function () use ($rows, $validated) {
            PenempatanPKL::insert($rows);

            if (! empty($validated['pembimbing_id'])) {
                PenempatanPKL::query()
                    ->where('bengkel_id', (int) $validated['bengkel_id'])
                    ->update([
                        'pembimbing_id' => $validated['pembimbing_id'],
                        'updated_at' => now(),
                    ]);
            }
        });

        Cache::forget('admin_dashboard_stats');
        Cache::forget('landing.map_data');
        Cache::forget('landing.table_data');
        if (! empty($validated['pembimbing_id'])) {
            Cache::forget("pembimbing:{$validated['pembimbing_id']}:dashboard_stats");
            Cache::forget("pembimbing:{$validated['pembimbing_id']}:monitoring_progress");
        }
        RekapAbsensiController::clearCetakCache(! empty($validated['pembimbing_id']) ? (int) $validated['pembimbing_id'] : null);

        return redirect()->route('admin.input_data_pkl.index')->with('message', 'Data penempatan PKL berhasil disimpan.');
    }

    public function show(Bengkel $bengkel)
    {
        $bengkel->load(['penempatan_pkl.siswa.kelas.jurusan', 'penempatan_pkl.pembimbing.jabatanPembimbing']);

        $pembimbings = $bengkel->penempatan_pkl
            ->pluck('pembimbing')
            ->filter()
            ->unique('id')
            ->values();

        return Inertia::render('Admin/InputDataPKL/Show', [
            'bengkel' => $bengkel,
            'pembimbings' => $pembimbings,
            'students' => $bengkel->penempatan_pkl->map(fn ($p) => $p->siswa),
        ]);
    }

    public function edit(Bengkel $bengkel)
    {
        $bengkel->load(['penempatan_pkl.siswa.kelas.jurusan', 'penempatan_pkl.pembimbing']);

        $currentStudentIds = $bengkel->penempatan_pkl
            ->pluck('siswa_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        $currentPembimbingId = $bengkel->penempatan_pkl->first()?->pembimbing_id;

        $currentStudents = $bengkel->penempatan_pkl
            ->map(fn ($placement) => $placement->siswa)
            ->filter()
            ->unique('id')
            ->values()
            ->map(function (Siswa $siswa) {
                return [
                    'id' => (int) $siswa->id,
                    'nama' => (string) $siswa->nama,
                    'kelas' => $siswa->kelas ? [
                        'id' => (int) $siswa->kelas->id,
                        'nama_kelas' => (string) $siswa->kelas->nama_kelas,
                        'jurusan' => $siswa->kelas->jurusan ? [
                            'id' => (int) $siswa->kelas->jurusan->id,
                            'nama_jurusan' => (string) $siswa->kelas->jurusan->nama_jurusan,
                        ] : null,
                    ] : null,
                ];
            });

        $kelas = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $pembimbings = Pembimbing::query()->select(['id', 'nama_guru'])->orderBy('nama_guru')->limit(500)->get();

        return Inertia::render('Admin/InputDataPKL/Edit', [
            'bengkel' => $bengkel,
            'kelasOptions' => $kelas,
            'siswaOptions' => [],
            'pembimbingOptions' => $pembimbings,
            'currentStudentIds' => $currentStudentIds,
            'currentPembimbingId' => $currentPembimbingId,
            'currentStudents' => $currentStudents,
        ]);
    }

    public function siswaOptions(Request $request)
    {
        $kelasId = (int) $request->input('kelas_id');
        $bengkelId = (int) $request->input('bengkel_id');
        $search = trim((string) $request->input('search', ''));

        if ($kelasId <= 0) {
            return response()->json([
                'data' => [],
            ]);
        }

        $placedSiswaSubquery = PenempatanPKL::query()
            ->when($bengkelId > 0, fn ($q) => $q->where('bengkel_id', '!=', $bengkelId))
            ->select('siswa_id')
            ->distinct();

        $query = Siswa::query()
            ->where('kelas_id', $kelasId)
            ->whereNotIn('id', $placedSiswaSubquery)
            ->with(['kelas.jurusan'])
            ->select('id', 'nama', 'kelas_id')
            ->orderBy('nama')
            ->limit(300);

        if ($search !== '') {
            $query->where('nama', 'like', '%'.$search.'%');
        }

        return response()->json([
            'data' => $query->get()->map(function (Siswa $siswa) {
                return [
                    'id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'kelas_id' => $siswa->kelas_id,
                    'kelas' => $siswa->kelas ? [
                        'id' => $siswa->kelas->id,
                        'nama_kelas' => $siswa->kelas->nama_kelas,
                        'jurusan' => $siswa->kelas->jurusan ? [
                            'id' => $siswa->kelas->jurusan->id,
                            'nama_jurusan' => $siswa->kelas->jurusan->nama_jurusan,
                        ] : null,
                    ] : null,
                ];
            })->values(),
        ]);
    }

    public function update(Request $request, Bengkel $bengkel)
    {
        $validated = $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswas,id',
            'pembimbing_id' => 'nullable|exists:pembimbings,id',
        ]);

        $siswaIds = collect($validated['siswa_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $alreadyPlacedInOther = PenempatanPKL::query()
            ->whereIn('siswa_id', $siswaIds)
            ->where('bengkel_id', '!=', $bengkel->id)
            ->with(['siswa:id,nama', 'bengkel:id,nama_bengkel'])
            ->get();

        if ($alreadyPlacedInOther->isNotEmpty()) {
            $conflicts = $alreadyPlacedInOther->map(function ($item) {
                $nama = $item->siswa?->nama ?? "Siswa #{$item->siswa_id}";
                $bengkelNama = $item->bengkel?->nama_bengkel ?? "Bengkel #{$item->bengkel_id}";
                return "{$nama} (sudah di {$bengkelNama})";
            })->implode(', ');

            return redirect()->back()->with('error', "Gagal memperbarui: Beberapa siswa sudah memiliki penempatan di bengkel lain: {$conflicts}");
        }

        $oldPembimbingIds = PenempatanPKL::query()->where('bengkel_id', $bengkel->id)
            ->whereNotNull('pembimbing_id')
            ->distinct()
            ->pluck('pembimbing_id')
            ->all();

        DB::transaction(function () use ($validated, $bengkel, $siswaIds) {
            PenempatanPKL::query()->where('bengkel_id', $bengkel->id)->delete();

            $now = now();
            $rows = $siswaIds->map(fn ($siswaId) => [
                'bengkel_id' => $bengkel->id,
                'siswa_id' => $siswaId,
                'pembimbing_id' => $validated['pembimbing_id'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            PenempatanPKL::insert($rows);
        });

        Cache::forget('admin_dashboard_stats');
        Cache::forget('landing.map_data');
        Cache::forget('landing.table_data');
        foreach ($oldPembimbingIds as $id) {
            Cache::forget("pembimbing:{$id}:dashboard_stats");
            Cache::forget("pembimbing:{$id}:monitoring_progress");
        }
        if (! empty($validated['pembimbing_id'])) {
            Cache::forget("pembimbing:{$validated['pembimbing_id']}:dashboard_stats");
            Cache::forget("pembimbing:{$validated['pembimbing_id']}:monitoring_progress");
            RekapAbsensiController::clearCetakCache((int) $validated['pembimbing_id']);
        }
        RekapAbsensiController::clearCetakCache();

        return redirect()->route('admin.input_data_pkl.index')->with('message', 'Data penempatan PKL berhasil diperbarui.');
    }

    public function bulkDestroy(Request $request)
    {
        $user = $request->user();
        abort_if(! $user, 403);
        abort_unless($user->hasAnyRole(['admin', 'super-admin']) || $user->can('input_data_pkl.bulk_delete'), 403);

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:bengkels,id',
        ]);

        $ids = collect($validated['ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $oldPembimbingIds = PenempatanPKL::query()->whereIn('bengkel_id', $ids)
            ->whereNotNull('pembimbing_id')
            ->distinct()
            ->pluck('pembimbing_id')
            ->all();

        PenempatanPKL::query()->whereIn('bengkel_id', $ids)->delete();

        Cache::forget('admin_dashboard_stats');
        Cache::forget('landing.map_data');
        Cache::forget('landing.table_data');
        foreach ($oldPembimbingIds as $id) {
            Cache::forget("pembimbing:{$id}:dashboard_stats");
            Cache::forget("pembimbing:{$id}:monitoring_progress");
            RekapAbsensiController::clearCetakCache($id);
        }
        RekapAbsensiController::clearCetakCache();

        return redirect()->back()->with('message', 'Data penempatan PKL terpilih berhasil dihapus.');
    }

    public function destroy(Bengkel $bengkel)
    {
        $oldPembimbingIds = PenempatanPKL::query()->where('bengkel_id', $bengkel->id)
            ->whereNotNull('pembimbing_id')
            ->distinct()
            ->pluck('pembimbing_id')
            ->all();

        PenempatanPKL::query()->where('bengkel_id', $bengkel->id)->delete();

        Cache::forget('admin_dashboard_stats');
        Cache::forget('landing.map_data');
        Cache::forget('landing.table_data');
        foreach ($oldPembimbingIds as $id) {
            Cache::forget("pembimbing:{$id}:dashboard_stats");
            Cache::forget("pembimbing:{$id}:monitoring_progress");
            RekapAbsensiController::clearCetakCache($id);
        }
        RekapAbsensiController::clearCetakCache();

        return redirect()->route('admin.input_data_pkl.index')->with('message', 'Data penempatan PKL berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new \App\Exports\PenempatanPKLTemplateExport, 'template_penempatan_pkl.xlsx');
    }

    public function exportExcel(Request $request)
    {
        $user = $request->user();
        abort_if(! $user, 403);
        abort_unless($user->hasAnyRole(['admin', 'super-admin']) || $user->can('input_data_pkl.export'), 403);

        $view = $request->input('view', 'bengkel');
        if (! in_array($view, ['bengkel', 'siswa_sudah', 'siswa_belum'], true)) {
            $view = 'bengkel';
        }

        $search = trim((string) $request->input('search', ''));
        $pembimbingFilter = $request->input('pembimbing_filter', 'all');
        if (! in_array($pembimbingFilter, ['all', 'with_pembimbing', 'without_pembimbing'], true)) {
            $pembimbingFilter = 'all';
        }

        if ($view === 'siswa_belum') {
            $placedSiswaSubquery = PenempatanPKL::query()->select('siswa_id')->distinct();

            $query = Siswa::query()
                ->whereNotIn('id', $placedSiswaSubquery)
                ->with(['kelas.jurusan'])
                ->select(['id', 'nama', 'nisn', 'kelas_id'])
                ->orderBy('nama');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', '%'.$search.'%')
                        ->orWhere('nisn', 'like', '%'.$search.'%');
                });
            }

            $rows = $query->get();

            return Excel::download(new SiswaBelumDapatBengkelExport($rows), 'Siswa_Belum_Dapat_Bengkel.xlsx');
        }

        if ($view === 'siswa_sudah') {
            $query = PenempatanPKL::query()
                ->with([
                    'siswa:id,nama,nisn,kelas_id',
                    'siswa.kelas:id,nama_kelas,jurusan_id',
                    'siswa.kelas.jurusan:id,nama_jurusan',
                    'bengkel:id,kode_bengkel,nama_bengkel',
                    'pembimbing:id,nama_guru',
                ])
                ->latest('id');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('siswa', function ($qs) use ($search) {
                        $qs->where('nama', 'like', '%'.$search.'%')
                            ->orWhere('nisn', 'like', '%'.$search.'%');
                    })->orWhereHas('bengkel', function ($qb) use ($search) {
                        $qb->where('nama_bengkel', 'like', '%'.$search.'%')
                            ->orWhere('kode_bengkel', 'like', '%'.$search.'%');
                    })->orWhereHas('pembimbing', function ($qp) use ($search) {
                        $qp->where('nama_guru', 'like', '%'.$search.'%');
                    });
                });
            }

            $rows = $query->get();

            return Excel::download(new SiswaSudahDapatBengkelExport($rows), 'Siswa_Sudah_Dapat_Bengkel.xlsx');
        }

        $query = Bengkel::whereHas('penempatan_pkl')
            ->with([
                'penempatan_pkl:id,bengkel_id,pembimbing_id',
                'penempatan_pkl.pembimbing:id,nama_guru',
            ])
            ->withCount('penempatan_pkl')
            ->orderBy('nama_bengkel');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_bengkel', 'like', "%{$search}%")
                    ->orWhere('kode_bengkel', 'like', "%{$search}%")
                    ->orWhereHas('penempatan_pkl.pembimbing', function ($qp) use ($search) {
                        $qp->where('nama_guru', 'like', "%{$search}%");
                    });
            });
        }

        if ($pembimbingFilter === 'with_pembimbing') {
            $query->whereHas('penempatan_pkl', fn ($q) => $q->whereNotNull('pembimbing_id'));
        } elseif ($pembimbingFilter === 'without_pembimbing') {
            $query->whereDoesntHave('penempatan_pkl', fn ($q) => $q->whereNotNull('pembimbing_id'));
        }

        $rows = $query->get();

        return Excel::download(new PenempatanPerBengkelExport($rows), 'Penempatan_PKL_Per_Bengkel.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $file = $request->file('file');
        $extension = strtolower((string) ($file?->getClientOriginalExtension() ?: $file?->extension()));
        if ($extension === 'xlsx' && ! class_exists(\ZipArchive::class)) {
            return redirect()->back()->with('error', 'Import gagal: server belum mengaktifkan ekstensi ZIP (ZipArchive). Aktifkan ext-zip atau gunakan file CSV.');
        }

        try {
            @ini_set('memory_limit', '512M');
            @set_time_limit(0);

            $importer = new \App\Imports\PenempatanPKLImport;
            Excel::import($importer, $file);

            Cache::forget('admin_dashboard_stats');

            $summary = $importer->summary();
            $successfulCount = (int) ($summary['inserted'] ?? 0) + (int) ($summary['updated'] ?? 0);
            $message = ($successfulCount > 0 ? 'Berhasil import penempatan PKL. ' : 'Import selesai. ')
                .'Ditambah: '.$summary['inserted'].', Diperbarui: '.$summary['updated'].', Dilewati: '.$summary['skipped'].'.';

            $errors = $summary['errors'] ?? [];
            if (! empty($errors)) {
                $errorText = 'Beberapa baris dilewati: '.implode(' | ', array_slice($errors, 0, 5));

                return redirect()->back()->with('message', $message)->with('error', $errorText);
            }

            if ($successfulCount === 0) {
                return redirect()->back()
                    ->with('message', $message)
                    ->with('error', 'Tidak ada data yang masuk. Pastikan memakai template, data di Sheet pertama, Kode/Nama Bengkel cocok dengan data Bengkel, dan Nama Siswa cocok dengan data Siswa (atau tambahkan kolom NISN 1..10).');
            }

            return redirect()->back()->with('message', $message);
        } catch (\Throwable $e) {
            Log::error('Import penempatan PKL gagal', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            $message = $e->getMessage();
            if (stripos($message, 'ZipArchive') !== false || stripos($message, 'zip') !== false) {
                $message = 'Server belum mengaktifkan ekstensi ZIP (ZipArchive) untuk membaca file .xlsx. Aktifkan ext-zip atau gunakan file CSV.';
            } elseif (stripos($message, 'Maximum execution time') !== false) {
                $message = 'Proses import melebihi batas waktu (timeout). Kurangi jumlah baris per import atau naikkan max_execution_time di hosting.';
            } elseif (stripos($message, 'Allowed memory size') !== false) {
                $message = 'Proses import kehabisan memori. Kurangi jumlah baris per import atau naikkan memory_limit di hosting.';
            }

            return redirect()->back()->with('error', 'Import gagal: '.$message);
        }
    }
}
