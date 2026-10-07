<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MutasiPKLExport;
use App\Http\Controllers\Controller;
use App\Models\Bengkel;
use App\Models\Kelas;
use App\Models\MutasiPKL;
use App\Models\Pembimbing;
use App\Models\PenempatanPKL;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class MutasiPKLController extends Controller
{
    public function index(Request $request)
    {
        $query = MutasiPKL::with([
            'siswa.kelas',
            'bengkelAsal',
            'bengkelTujuan:id,nama_bengkel,dusun,desa,kecamatan,deskripsi,google_maps_link',
            'pembimbing',
            'pembimbingTujuan',
            'admin:id,name',
        ]);

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        if ($request->has('kelas_id') && $request->kelas_id) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        $perPage = $request->input('per_page', 10);
        $mutasi = $query->latest()->paginate($perPage)->withQueryString();

        $bengkelPembimbingMap = PenempatanPKL::query()
            ->whereNotNull('bengkel_id')
            ->whereNotNull('pembimbing_id')
            ->select('bengkel_id', 'pembimbing_id')
            ->distinct()
            ->get()
            ->pluck('pembimbing_id', 'bengkel_id');

        $kelasOptions = Kelas::query()->select(['id', 'nama_kelas'])->orderBy('nama_kelas')->get();
        $bengkelOptions = Bengkel::query()->select(['id', 'nama_bengkel', 'kode_bengkel', 'dusun', 'desa', 'kecamatan', 'nama_pemilik'])
            ->orderBy('nama_bengkel')
            ->get()
            ->map(function ($b) use ($bengkelPembimbingMap) {
                $b->assigned_pembimbing_id = $bengkelPembimbingMap[$b->id] ?? null;
                return $b;
            });
        $pembimbingOptions = Pembimbing::query()->select(['id', 'nama_guru'])->orderBy('nama_guru')->get();

        return Inertia::render('Admin/MutasiPKL/Index', [
            'mutasi' => $mutasi,
            'filters' => $request->all(['search', 'per_page', 'kelas_id']),
            'kelasOptions' => $kelasOptions,
            'bengkelOptions' => $bengkelOptions,
            'pembimbingOptions' => $pembimbingOptions,
        ]);
    }

    public function export(Request $request)
    {
        $filters = $request->all(['search', 'kelas_id']);

        return Excel::download(new MutasiPKLExport($filters), 'data_mutasi_pkl.xlsx');
    }

    public function create()
    {
        $kelasOptions = Kelas::query()->select(['id', 'nama_kelas'])
            ->orderBy('nama_kelas')
            ->get();

        $bengkels = Bengkel::query()->select(['id', 'nama_bengkel', 'kode_bengkel'])->orderBy('nama_bengkel')->get();
        $pembimbings = Pembimbing::query()->select(['id', 'nama_guru'])->orderBy('nama_guru')->get();

        return Inertia::render('Admin/MutasiPKL/Create', [
            'kelasOptions' => $kelasOptions,
            'siswaOptions' => [],
            'bengkelOptions' => $bengkels,
            'pembimbingOptions' => $pembimbings,
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
            ->with([
                'kelas.jurusan',
                'penempatan.bengkel:id,nama_bengkel,kode_bengkel',
            ])
            ->select('id', 'nama', 'kelas_id')
            ->where('kelas_id', $kelasId)
            ->whereHas('penempatan')
            ->orderBy('nama')
            ->limit(300);

        if ($search !== '') {
            $query->where('nama', 'like', '%'.$search.'%');
        }

        return response()->json([
            'data' => $query->get()->map(function ($siswa) {
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
                    'current_bengkel' => $siswa->penempatan && $siswa->penempatan->bengkel ? [
                        'id' => $siswa->penempatan->bengkel->id,
                        'nama_bengkel' => $siswa->penempatan->bengkel->nama_bengkel,
                        'kode_bengkel' => $siswa->penempatan->bengkel->kode_bengkel,
                    ] : null,
                ];
            })->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'siswa_id' => 'required|exists:siswas,id',
            'bengkel_asal_id' => 'required|exists:bengkels,id',
            'bengkel_tujuan_id' => 'required|exists:bengkels,id',
            'pembimbing_id' => 'required|exists:pembimbings,id',
            'alasan' => 'required|string',
        ]);

        $kelasSiswaMatch = Siswa::query()->where('id', $validated['siswa_id'])
            ->where('kelas_id', $validated['kelas_id'])
            ->exists();

        if (! $kelasSiswaMatch) {
            return back()->withErrors(['siswa_id' => 'Siswa tidak sesuai dengan kelas yang dipilih.']);
        }

        if ($validated['bengkel_asal_id'] == $validated['bengkel_tujuan_id']) {
            return back()->withErrors(['bengkel_tujuan_id' => 'Bengkel tujuan harus berbeda dengan bengkel asal.']);
        }

        MutasiPKL::create([
            'siswa_id' => $validated['siswa_id'],
            'bengkel_asal_id' => $validated['bengkel_asal_id'],
            'bengkel_tujuan_id' => $validated['bengkel_tujuan_id'],
            'pembimbing_id' => $validated['pembimbing_id'],
            'alasan' => $validated['alasan'],
            'status' => 'pending',
            'admin_id' => Auth::id(),
        ]);

        Cache::forget('mutasi_pending_count');
        Cache::forget('admin_dashboard_stats');

        return redirect()->route('admin.mutasi_pkl.index')->with('message', 'Pengajuan mutasi berhasil dibuat.');
    }

    public function process(Request $request, MutasiPKL $mutasi)
    {
        if ($mutasi->status !== 'pending') {
            return back()->with('error', 'Mutasi sudah diproses.');
        }

        $validated = $request->validate([
            'admin_note' => 'nullable|string',
        ]);

        $mutasi->update([
            'status' => 'processing',
            'admin_note' => $validated['admin_note'] ?? null,
            'admin_id' => Auth::id(),
        ]);

        Cache::forget('mutasi_pending_count');
        Cache::forget('admin_dashboard_stats');

        return back()->with('message', 'Pengajuan mutasi diproses (Laporan Diterima).');
    }

    public function review(MutasiPKL $mutasi)
    {
        $mutasi->load([
            'siswa.kelas.jurusan',
            'bengkelAsal',
            'bengkelTujuan:id,nama_bengkel,dusun,desa,kecamatan,deskripsi,google_maps_link',
            'pembimbing',
            'pembimbingTujuan',
            'admin:id,name',
        ]);

        $bengkelPembimbingMap = PenempatanPKL::query()
            ->whereNotNull('bengkel_id')
            ->whereNotNull('pembimbing_id')
            ->select('bengkel_id', 'pembimbing_id')
            ->distinct()
            ->get()
            ->pluck('pembimbing_id', 'bengkel_id');

        $bengkelOptions = Bengkel::query()->select(['id', 'nama_bengkel', 'kode_bengkel', 'dusun', 'desa', 'kecamatan', 'nama_pemilik', 'google_maps_link', 'deskripsi'])
            ->orderBy('nama_bengkel')
            ->get()
            ->map(function ($b) use ($bengkelPembimbingMap) {
                $b->assigned_pembimbing_id = $bengkelPembimbingMap[$b->id] ?? null;
                return $b;
            });

        $pembimbingOptions = Pembimbing::query()->select(['id', 'nama_guru'])->orderBy('nama_guru')->get();

        return Inertia::render('Admin/MutasiPKL/Review', [
            'mutasi' => $mutasi,
            'bengkelOptions' => $bengkelOptions,
            'pembimbingOptions' => $pembimbingOptions,
        ]);
    }

    public function approve(Request $request, MutasiPKL $mutasi)
    {
        if ($mutasi->status !== 'processing' && $mutasi->status !== 'pending') {
            return back()->with('error', 'Mutasi harus dalam status pending atau processing.');
        }

        $validated = $request->validate([
            'admin_note' => 'nullable|string',
            'bengkel_mode' => 'nullable|in:existing,new',
            'bengkel_tujuan_id' => 'nullable|exists:bengkels,id',
            'pembimbing_tujuan_id' => 'nullable|exists:pembimbings,id',
            'pembimbing_id' => 'nullable|exists:pembimbings,id',
            'nama_bengkel_baru' => 'nullable|string|max:255',
            'nama_pemilik_bengkel_baru' => 'nullable|string|max:255',
            'dusun' => 'nullable|string|max:255',
            'desa' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'google_maps_link_baru' => 'nullable|string',
        ]);

        $bengkelMode = $validated['bengkel_mode'] ?? ($request->filled('bengkel_tujuan_id') ? 'existing' : 'new');
        $targetBengkelId = null;

        if ($bengkelMode === 'existing' && ! empty($validated['bengkel_tujuan_id'])) {
            $targetBengkelId = $validated['bengkel_tujuan_id'];
        } else {
            // Check if Admin provided corrected data or use mutasi original data
            $namaBengkel = $validated['nama_bengkel_baru'] ?? $mutasi->nama_bengkel_baru ?? 'Bengkel Baru (Mutasi)';

            // Check case-insensitive if exact match already exists to prevent duplication
            $existing = Bengkel::query()->whereRaw('LOWER(nama_bengkel) = ?', [strtolower(trim($namaBengkel))])->first();

            if ($existing) {
                $targetBengkelId = $existing->id;
            } else {
                $newBengkel = Bengkel::create([
                    'nama_bengkel' => $namaBengkel,
                    'nama_pemilik' => $validated['nama_pemilik_bengkel_baru'] ?? $mutasi->nama_pemilik_bengkel_baru ?? null,
                    'dusun' => $validated['dusun'] ?? $mutasi->dusun,
                    'desa' => $validated['desa'] ?? $mutasi->desa,
                    'kecamatan' => $validated['kecamatan'] ?? $mutasi->kecamatan,
                    'deskripsi' => $validated['deskripsi'] ?? $mutasi->deskripsi,
                    'google_maps_link' => $validated['google_maps_link_baru'] ?? $mutasi->google_maps_link_baru,
                ]);
                $targetBengkelId = $newBengkel->id;
            }
        }

        // Check if target bengkel already has an assigned pembimbing in penempatan_p_k_l_s
        $existingBengkelPembimbingId = PenempatanPKL::query()
            ->where('bengkel_id', $targetBengkelId)
            ->whereNotNull('pembimbing_id')
            ->value('pembimbing_id');

        // Prevent 1 bengkel from having 2 pembimbing
        $pembimbingTujuanId = $existingBengkelPembimbingId
            ?: ($validated['pembimbing_tujuan_id'] ?? $validated['pembimbing_id'] ?? null);

        // Keep pembimbing_id as Pembimbing Asal (Lama) for Surat P-1
        $pembimbingAsalId = $mutasi->pembimbing_id;
        if (! $pembimbingAsalId) {
            $currentPlacement = PenempatanPKL::query()->where('siswa_id', $mutasi->siswa_id)->first();
            $pembimbingAsalId = $currentPlacement?->pembimbing_id;
        }

        $mutasi->update([
            'bengkel_tujuan_id' => $targetBengkelId,
            'pembimbing_id' => $pembimbingAsalId,
            'pembimbing_tujuan_id' => $pembimbingTujuanId,
            'status' => 'approved',
            'admin_note' => $validated['admin_note'] ?? null,
            'tanggal_surat' => now(),
            'admin_id' => Auth::id(),
        ]);

        // Update PenempatanPKL to target bengkel and target pembimbing
        $placement = PenempatanPKL::query()->where('siswa_id', $mutasi->siswa_id)->first();
        if ($placement) {
            $placement->update([
                'bengkel_id' => $targetBengkelId,
                'pembimbing_id' => $pembimbingTujuanId,
            ]);
        }

        Cache::forget('mutasi_pending_count');
        Cache::forget('admin_dashboard_stats');
        if ($pembimbingAsalId) {
            Cache::forget("pembimbing:{$pembimbingAsalId}:dashboard_stats");
            Cache::forget("pembimbing:{$pembimbingAsalId}:monitoring_progress");
        }
        if ($pembimbingTujuanId && $pembimbingTujuanId !== $pembimbingAsalId) {
            Cache::forget("pembimbing:{$pembimbingTujuanId}:dashboard_stats");
            Cache::forget("pembimbing:{$pembimbingTujuanId}:monitoring_progress");
        }

        return redirect()->route('admin.mutasi_pkl.index')->with('message', 'Mutasi disetujui, tanggal surat dibuat, dan data penempatan diperbarui.');
    }

    public function reject(Request $request, MutasiPKL $mutasi)
    {
        if ($mutasi->status !== 'pending') {
            return back()->with('error', 'Mutasi sudah diproses.');
        }

        $validated = $request->validate([
            'admin_note' => 'required|string',
        ]);

        $mutasi->update([
            'status' => 'rejected',
            'admin_note' => $validated['admin_note'],
            'admin_id' => Auth::id(),
        ]);

        Cache::forget('mutasi_pending_count');
        Cache::forget('admin_dashboard_stats');

        return back()->with('message', 'Mutasi ditolak.');
    }

    public function update(Request $request, MutasiPKL $mutasi)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'siswa_id' => 'required|exists:siswas,id',
            'bengkel_asal_id' => 'required|exists:bengkels,id',
            'bengkel_tujuan_id' => 'required|exists:bengkels,id',
            'pembimbing_id' => 'required|exists:pembimbings,id',
            'alasan' => 'required|string',
        ]);

        $mutasi->update($validated);

        Cache::forget('mutasi_pending_count');
        Cache::forget('admin_dashboard_stats');

        return redirect()->route('admin.mutasi_pkl.index')->with('message', 'Data mutasi berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $mutasi = MutasiPKL::findOrFail($id);
        $mutasi->delete();

        Cache::forget('mutasi_pending_count');
        Cache::forget('admin_dashboard_stats');

        return back()->with('message', 'Data mutasi dihapus.');
    }
}
