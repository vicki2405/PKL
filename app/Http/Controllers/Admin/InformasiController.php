<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Informasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class InformasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Informasi::query();

        if ($request->has('search') && $request->search) {
            $query->where('judul', 'like', '%'.$request->search.'%')
                ->orWhere('deskripsi', 'like', '%'.$request->search.'%');
        }

        $perPage = $request->input('per_page', 10);
        $validPerPage = [10, 20, 50, 100];

        if (! in_array($perPage, $validPerPage)) {
            $perPage = 10;
        }

        $informasis = $query->latest('tanggal_terbit')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Informasi/Index', [
            'informasis' => $informasis,
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Informasi/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'status' => 'required|boolean',
            'tanggal_terbit' => 'required|date',
        ]);

        Informasi::create($validated);

        Cache::forget('pembimbing:informasis:active');
        Cache::forget('informasis:active:latest5');

        return redirect()->route('admin.informasi.index')
            ->with('success', 'Informasi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Informasi $informasi)
    {
        return Inertia::render('Admin/Informasi/Edit', [
            'informasi' => $informasi,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Informasi $informasi)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'status' => 'required|boolean',
            'tanggal_terbit' => 'required|date',
        ]);

        $informasi->update($validated);

        Cache::forget('pembimbing:informasis:active');
        Cache::forget('informasis:active:latest5');

        return redirect()->route('admin.informasi.index')
            ->with('success', 'Informasi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Informasi $informasi)
    {
        $informasi->delete();

        Cache::forget('pembimbing:informasis:active');
        Cache::forget('informasis:active:latest5');

        return redirect()->route('admin.informasi.index')
            ->with('success', 'Informasi berhasil dihapus.');
    }
}
