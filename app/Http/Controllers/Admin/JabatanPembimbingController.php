<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JabatanPembimbing;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JabatanPembimbingController extends Controller
{
    public function index()
    {
        $jabatans = JabatanPembimbing::latest()->paginate(10);

        return Inertia::render('Admin/Pembimbing/Jabatan', [
            'jabatans' => $jabatans,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        JabatanPembimbing::create($validated);

        return redirect()->back()->with('message', 'Jabatan pembimbing berhasil ditambahkan.');
    }

    public function update(Request $request, JabatanPembimbing $jabatan_pembimbing)
    {
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $jabatan_pembimbing->update($validated);

        return redirect()->back()->with('message', 'Jabatan pembimbing berhasil diperbarui.');
    }

    public function destroy(JabatanPembimbing $jabatan_pembimbing)
    {
        $jabatan_pembimbing->delete();

        return redirect()->back()->with('message', 'Jabatan pembimbing berhasil dihapus.');
    }
}
