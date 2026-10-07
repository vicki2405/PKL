<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MutasiPKL;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CetakController extends Controller
{
    public function indexP1(Request $request)
    {
        $query = MutasiPKL::with(['siswa.kelas', 'bengkelAsal', 'bengkelTujuan', 'pembimbing', 'pembimbingTujuan'])
            ->where('status', 'approved');

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $mutasi = $query->latest()->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/Cetak/P1', [
            'mutasi' => $mutasi,
            'filters' => $request->all(['search', 'per_page']),
        ]);
    }

    public function printP1(Request $request, $id)
    {
        $mutasi = MutasiPKL::with(['siswa.kelas.jurusan', 'bengkelAsal', 'bengkelTujuan', 'pembimbing', 'pembimbingTujuan'])->findOrFail($id);

        // Use saved tanggal_surat or fallback to current date
        $tanggalSurat = $mutasi->tanggal_surat
            ? \Carbon\Carbon::parse($mutasi->tanggal_surat)->translatedFormat('d F Y')
            : now()->translatedFormat('d F Y');

        $pdf = Pdf::loadView('pdf.p1', compact('mutasi', 'tanggalSurat'))
            ->setPaper([0, 0, 609.4488, 935.433], 'portrait');

        $namaSiswa = $mutasi->siswa?->nama ? str_replace(['/', '\\'], '_', $mutasi->siswa->nama) : 'Siswa';
        return $pdf->stream('Surat_P1_'.$namaSiswa.'.pdf');
    }

    public function destroyP1(MutasiPKL $mutasi)
    {
        if (! auth()->user()?->hasAnyRole(['admin', 'super-admin'])) {
            abort(403);
        }

        if (! auth()->user()?->can('cetak_p1.delete')) {
            abort(403);
        }

        if ($mutasi->status !== 'approved') {
            abort(403);
        }

        $mutasi->delete();

        return back()->with('message', 'Data P1 berhasil dihapus.');
    }
}
