<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MutasiPKL;
use App\Models\Pembimbing;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonitoringPenempatanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $pembimbingId = $request->input('pembimbing_id');
        $statusPindah = $request->input('status_pindah', 'all'); // 'all', 'pernah_pindah', 'belum_pindah'

        $query = Siswa::query()
            ->with([
                'kelas.jurusan',
                'penempatan.bengkel',
                'penempatan.pembimbing',
                'mutasis' => function ($q) {
                    $q->where('status', 'approved')
                        ->with(['bengkelAsal', 'bengkelTujuan', 'pembimbingAsal', 'pembimbingTujuan'])
                        ->latest();
                },
            ]);

        // Filter status pindah (pernah mutasi / belum pernah)
        if ($statusPindah === 'pernah_pindah') {
            $query->whereHas('mutasis', function ($q) {
                $q->where('status', 'approved');
            });
        } elseif ($statusPindah === 'belum_pindah') {
            $query->whereDoesntHave('mutasis', function ($q) {
                $q->where('status', 'approved');
            });
        }

        // Filter guru pembimbing
        if ($pembimbingId) {
            $query->where(function ($q) use ($pembimbingId) {
                $q->whereHas('penempatan', function ($sub) use ($pembimbingId) {
                    $sub->where('pembimbing_id', $pembimbingId);
                })->orWhereHas('mutasis', function ($sub) use ($pembimbingId) {
                    $sub->where('status', 'approved')
                        ->where(function ($m) use ($pembimbingId) {
                            $m->where('pembimbing_id', $pembimbingId)
                              ->orWhere('pembimbing_tujuan_id', $pembimbingId);
                        });
                });
            });
        }

        // Filter pencarian nama siswa, NISN, atau nama bengkel
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhereHas('penempatan.bengkel', function ($b) use ($search) {
                        $b->where('nama_bengkel', 'like', "%{$search}%");
                    })
                    ->orWhereHas('mutasis.bengkelAsal', function ($b) use ($search) {
                        $b->where('nama_bengkel', 'like', "%{$search}%");
                    });
            });
        }

        $siswaList = $query->orderBy('nama', 'asc')->paginate(15)->withQueryString();

        $totalSiswa = Siswa::query()->count('*');
        $totalPernahPindah = MutasiPKL::query()->where('status', 'approved')->distinct()->count('siswa_id');

        $pembimbingOptions = Pembimbing::query()
            ->select(['id', 'nama_guru'])
            ->orderBy('nama_guru')
            ->get();

        return Inertia::render('Admin/MonitoringPenempatan/Index', [
            'siswaList' => $siswaList,
            'pembimbingOptions' => $pembimbingOptions,
            'filters' => [
                'search' => $search ?? '',
                'pembimbing_id' => $pembimbingId ?? '',
                'status_pindah' => $statusPindah,
            ],
            'statistics' => [
                'totalSiswa' => $totalSiswa,
                'totalPernahPindah' => $totalPernahPindah,
                'totalBelumPindah' => $totalSiswa - $totalPernahPindah,
            ],
        ]);
    }
}
