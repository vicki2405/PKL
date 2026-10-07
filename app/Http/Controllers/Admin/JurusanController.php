<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class JurusanController extends Controller
{
    public function index(Request $request)
    {
        $query = Jurusan::query();

        if ($request->search) {
            $query->where('nama_jurusan', 'like', '%'.$request->search.'%')
                ->orWhere('kode_jurusan', 'like', '%'.$request->search.'%');
        }

        $jurusans = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Jurusan/Index', [
            'jurusans' => $jurusans,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_jurusan' => 'required|string|unique:jurusans,kode_jurusan',
            'nama_jurusan' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        Jurusan::create($validated);

        Cache::forget('options.jurusan');
        Cache::forget('options.kelas_with_jurusan');

        return redirect()->back()->with('message', 'Jurusan berhasil ditambahkan.');
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $validated = $request->validate([
            'kode_jurusan' => 'required|string|unique:jurusans,kode_jurusan,'.$jurusan->id,
            'nama_jurusan' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $jurusan->update($validated);

        Cache::forget('options.jurusan');
        Cache::forget('options.kelas_with_jurusan');

        return redirect()->back()->with('message', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan)
    {
        $jurusan->delete();

        Cache::forget('options.jurusan');
        Cache::forget('options.kelas_with_jurusan');

        return redirect()->back()->with('message', 'Jurusan berhasil dihapus.');
    }
}
