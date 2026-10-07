<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RekapProblemExport;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pembimbing;
use App\Models\PenempatanPKL;
use App\Models\RekapProblem;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class RekapProblemController extends Controller
{
    public function index(Request $request)
    {
        $query = RekapProblem::with(['siswa.kelas', 'bengkel', 'pembimbing', 'admin:id,name']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa', function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%");
                })->orWhereHas('bengkel', function ($q) use ($search) {
                    $q->where('nama_bengkel', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        if ($request->filled('pembimbing_id')) {
            $query->where('pembimbing_id', $request->pembimbing_id);
        }

        $perPage = $request->input('per_page', 10);
        $problems = $query->latest('tanggal')->paginate($perPage)->withQueryString();

        $todayDate = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $stats = [
            'total' => RekapProblem::query()->count('*'),
            'today' => RekapProblem::query()->where('tanggal', $todayDate)->count('*'),
            'month' => RekapProblem::query()->whereBetween('tanggal', [$monthStart, $monthEnd])->count('*'),
        ];

        $kelasOptions = Cache::remember('options.kelas_labels', 86400, function () {
            return Kelas::query()->orderByNamaKelas()
                ->get(['id', 'nama_kelas'])
                ->map(fn ($k) => ['id' => $k->id, 'label' => $k->nama_kelas])
                ->values();
        });
        $pembimbingOptions = Cache::remember('options.pembimbing_labels', 86400, function () {
            return Pembimbing::query()->orderBy('nama_guru')
                ->get(['id', 'nama_guru'])
                ->map(fn ($p) => ['id' => $p->id, 'label' => $p->nama_guru])
                ->values();
        });

        return Inertia::render('Admin/RekapProblem/Index', [
            'problems' => $problems,
            'stats' => $stats,
            'filters' => $request->all(['search', 'per_page', 'tanggal', 'kelas_id', 'pembimbing_id']),
            'kelasOptions' => $kelasOptions,
            'pembimbingOptions' => $pembimbingOptions,
        ]);
    }

    public function create()
    {
        $kelasOptions = Cache::remember('options.kelas_labels', 86400, function () {
            return Kelas::query()->orderByNamaKelas()
                ->get(['id', 'nama_kelas'])
                ->map(fn ($k) => ['id' => $k->id, 'label' => $k->nama_kelas])
                ->values()
                ->all();
        });

        return Inertia::render('Admin/RekapProblem/Create', [
            'kelasOptions' => $kelasOptions,
            'siswaOptions' => [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'siswa_id' => 'required|exists:siswas,id',
            'deskripsi_masalah' => 'required|string',
            'deskripsi_tindakan' => 'required|string',
            'saksi' => 'nullable|string',
        ]);

        // Auto-fetch Bengkel and Pembimbing from current placement
        $placement = PenempatanPKL::query()->where('siswa_id', $validated['siswa_id'])->latest()->first();

        if (! $placement) {
            return back()->withErrors(['siswa_id' => 'Siswa ini tidak memiliki penempatan PKL aktif.']);
        }

        RekapProblem::create([
            'tanggal' => $validated['tanggal'],
            'siswa_id' => $validated['siswa_id'],
            'bengkel_id' => $placement->bengkel_id,
            'pembimbing_id' => $placement->pembimbing_id,
            'deskripsi_masalah' => $validated['deskripsi_masalah'],
            'deskripsi_tindakan' => $validated['deskripsi_tindakan'],
            'saksi' => $validated['saksi'],
            'admin_id' => Auth::id(),
        ]);

        return redirect()->route('admin.rekap_problem.index')->with('message', 'Data masalah berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $problem = RekapProblem::with(['siswa.kelas', 'bengkel', 'pembimbing'])->findOrFail($id);

        $kelasOptions = Cache::remember('options.kelas_labels', 86400, function () {
            return Kelas::query()->orderByNamaKelas()
                ->get(['id', 'nama_kelas'])
                ->map(fn ($k) => ['id' => $k->id, 'label' => $k->nama_kelas])
                ->values()
                ->all();
        });

        return Inertia::render('Admin/RekapProblem/Edit', [
            'problem' => $problem,
            'siswaOptions' => [],
            'kelasOptions' => $kelasOptions,
        ]);
    }

    public function siswaOptions(Request $request)
    {
        $kelasId = (int) $request->input('kelas_id');
        $search = trim((string) $request->input('search', ''));

        if ($kelasId <= 0) {
            return response()->json([
                'data' => [],
            ]);
        }

        $query = Siswa::query()
            ->where('kelas_id', $kelasId)
            ->whereHas('penempatan')
            ->with(['kelas:id,nama_kelas', 'penempatan.bengkel:id,nama_bengkel,dusun,desa,kecamatan', 'penempatan.pembimbing:id,nama_guru'])
            ->orderBy('nama')
            ->limit(300);

        if ($search !== '') {
            $query->where('nama', 'like', '%'.$search.'%');
        }

        return response()->json([
            'data' => $query->get()->map(function ($siswa) {
                $placement = $siswa->penempatan;

                return [
                    'id' => $siswa->id,
                    'kelas_id' => $siswa->kelas ? $siswa->kelas->id : null,
                    'nama' => $siswa->nama,
                    'kelas_nama' => $siswa->kelas ? $siswa->kelas->nama_kelas : '-',
                    'bengkel' => $placement && $placement->bengkel ? $placement->bengkel : null,
                    'pembimbing' => $placement && $placement->pembimbing ? $placement->pembimbing : null,
                ];
            })->values(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $problem = RekapProblem::findOrFail($id);

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'siswa_id' => 'required|exists:siswas,id',
            'deskripsi_masalah' => 'required|string',
            'deskripsi_tindakan' => 'required|string',
            'saksi' => 'nullable|string',
        ]);

        $placement = PenempatanPKL::query()->where('siswa_id', $validated['siswa_id'])->latest()->first();

        if (! $placement) {
            return back()->withErrors(['siswa_id' => 'Siswa ini tidak memiliki penempatan PKL aktif.']);
        }

        $problem->update([
            'tanggal' => $validated['tanggal'],
            'siswa_id' => $validated['siswa_id'],
            'bengkel_id' => $placement->bengkel_id,
            'pembimbing_id' => $placement->pembimbing_id,
            'deskripsi_masalah' => $validated['deskripsi_masalah'],
            'deskripsi_tindakan' => $validated['deskripsi_tindakan'],
            'saksi' => $validated['saksi'],
        ]);

        return redirect()->route('admin.rekap_problem.index')->with('message', 'Data masalah berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $problem = RekapProblem::findOrFail($id);
        $problem->delete();

        return redirect()->route('admin.rekap_problem.index')->with('message', 'Data masalah berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $filters = $request->all(['search', 'tanggal', 'kelas_id', 'pembimbing_id']);
        $permission = $request->type === 'pdf' ? 'rekap_problem.export_pdf' : 'rekap_problem.export_excel';

        abort_unless($request->user()?->can($permission), 403);

        @ini_set('memory_limit', '512M');
        @set_time_limit(0);

        if ($request->type === 'pdf') {
            $query = RekapProblem::query()->with(['siswa.kelas', 'bengkel', 'pembimbing', 'admin']);

            if (! empty($filters['tanggal'])) {
                $query->whereDate('tanggal', $filters['tanggal']);
            }
            if (! empty($filters['kelas_id'])) {
                $query->whereHas('siswa', function ($q) use ($filters) {
                    $q->where('kelas_id', $filters['kelas_id']);
                });
                $filters['kelas_nama'] = Kelas::find($filters['kelas_id'])?->nama_kelas;
            }
            if (! empty($filters['pembimbing_id'])) {
                $query->where('pembimbing_id', $filters['pembimbing_id']);
                $filters['pembimbing_nama'] = Pembimbing::find($filters['pembimbing_id'])?->nama_guru;
            }
            if (! empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->whereHas('siswa', function ($q) use ($search) {
                        $q->where('nama', 'like', '%'.$search.'%');
                    })
                        ->orWhereHas('bengkel', function ($q) use ($search) {
                            $q->where('nama_bengkel', 'like', '%'.$search.'%');
                        });
                });
            }

            $allData = collect();
            $query->latest('tanggal')->chunk(200, function ($chunk) use (&$allData) {
                $allData = $allData->concat($chunk);
            });

            $pdf = Pdf::loadView('pdf.rekap_problem', ['data' => $allData, 'filters' => $filters])->setPaper('a4', 'landscape');

            return $pdf->download('rekap_problem.pdf');
        }

        return Excel::download(new RekapProblemExport($filters), 'rekap_problem.xlsx');
    }
}
