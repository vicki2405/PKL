<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Informasi;
use App\Models\MutasiPKL;
use App\Models\Pembimbing;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class InformasiController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $pembimbing = Pembimbing::where('user_id', $user->id)->firstOrFail();

        $informasis = Cache::remember('pembimbing:informasis:active', 300, function () {
            return Informasi::query()
                ->where('status', true)
                ->select(['id', 'judul', 'deskripsi', 'tanggal_terbit'])
                ->latest('tanggal_terbit')
                ->get();
        });

        $mutasis = MutasiPKL::with([
            'siswa:id,nama',
            'bengkelAsal:id,nama_bengkel',
            'bengkelTujuan:id,nama_bengkel',
        ])
            ->where('pembimbing_id', $pembimbing->id)
            ->latest()
            ->get();

        return Inertia::render('Pembimbing/Informasi/Index', [
            'informasis' => $informasis,
            'mutasis' => $mutasis,
        ]);
    }
}
