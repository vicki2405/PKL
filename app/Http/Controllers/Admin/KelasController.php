<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $query = Kelas::query()->with('jurusan');

        if ($request->nama_kelas) {
            $query->where('nama_kelas', 'like', '%'.$request->nama_kelas.'%');
        }

        if ($request->jurusan_id) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        $perPage = (int) $request->input('per_page', 5);
        if ($perPage <= 0) {
            $perPage = 5;
        }

        $kelas = $query->orderByNamaKelas('asc')->paginate($perPage)->withQueryString();

        $jurusanOptions = Cache::remember('options.jurusan', 86400, function () {
            return Jurusan::query()->select(['id', 'kode_jurusan', 'nama_jurusan'])
                ->orderBy('nama_jurusan')
                ->get();
        });

        return Inertia::render('Admin/Kelas/Index', [
            'kelas' => $kelas,
            'jurusanOptions' => $jurusanOptions,
            'filters' => array_merge($request->only(['nama_kelas', 'jurusan_id']), ['per_page' => $perPage]),
        ]);
    }

    public function create()
    {
        $jurusanOptions = Jurusan::query()->select(['id', 'kode_jurusan', 'nama_jurusan'])
            ->orderBy('nama_jurusan')
            ->get();

        return Inertia::render('Admin/Kelas/Create', [
            'jurusanOptions' => $jurusanOptions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jurusan_id' => 'required|exists:jurusans,id',
            'nama_kelas' => 'required|string|max:255',
            'tingkat' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
            'wali_kelas' => 'nullable|string|max:255',
            'no_wa_wali' => 'nullable|string|max:50',
        ]);

        Kelas::create($validated);

        Cache::forget('options.kelas');
        Cache::forget('options.kelas_labels');
        Cache::forget('options.kelas_with_jurusan');

        return redirect()->route('admin.kelas.index')->with('message', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kela)
    {
        $jurusanOptions = Jurusan::query()->select(['id', 'kode_jurusan', 'nama_jurusan'])
            ->orderBy('nama_jurusan')
            ->get();

        return Inertia::render('Admin/Kelas/Edit', [
            'kelas' => $kela->load('jurusan'),
            'jurusanOptions' => $jurusanOptions,
        ]);
    }

    public function update(Request $request, Kelas $kela)
    {
        $validated = $request->validate([
            'jurusan_id' => 'required|exists:jurusans,id',
            'nama_kelas' => 'required|string|max:255',
            'tingkat' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
            'wali_kelas' => 'nullable|string|max:255',
            'no_wa_wali' => 'nullable|string|max:50',
        ]);

        $kela->update($validated);

        Cache::forget('options.kelas');
        Cache::forget('options.kelas_labels');
        Cache::forget('options.kelas_with_jurusan');

        return redirect()->route('admin.kelas.index')->with('message', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kela)
    {
        $kela->delete();

        Cache::forget('options.kelas');
        Cache::forget('options.kelas_labels');
        Cache::forget('options.kelas_with_jurusan');

        return redirect()->back()->with('message', 'Kelas berhasil dihapus.');
    }
}
