<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bengkel;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\MutasiPKL;
use App\Models\Pembimbing;
use App\Models\PenempatanPKL;
use App\Models\Siswa;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('admin_dashboard_stats', 300, function () {
            $totalSiswa = Siswa::query()->count('*');
            $siswaSudahPKL = PenempatanPKL::query()->distinct()->count('siswa_id');

            $mutasiPending = MutasiPKL::query()->where('status', 'pending')->count('*');
            $mutasiProcessing = MutasiPKL::query()->where('status', 'processing')->count('*');
            $mutasiApproved = MutasiPKL::query()->where('status', 'approved')->count('*');
            $mutasiRejected = MutasiPKL::query()->where('status', 'rejected')->count('*');

            $bengkelTKR = Bengkel::query()->where('kode_bengkel', 'TKR')->count('*');
            $bengkelTSM = Bengkel::query()->where('kode_bengkel', 'TSM')->count('*');
            $bengkelTEI = Bengkel::query()->where('kode_bengkel', 'TEI')->count('*');
            $bengkelANS = Bengkel::query()->where('kode_bengkel', 'ANS')->count('*');
            $bengkelKLR = Bengkel::query()->where('kode_bengkel', 'KLR')->count('*');
            $bengkelLain = Bengkel::query()->whereNotIn('kode_bengkel', ['TKR', 'TSM', 'TEI', 'ANS', 'KLR'], 'and')
                ->orWhereNull('kode_bengkel')
                ->count('*');

            return [
                'totalJurusan' => Jurusan::query()->count('*'),
                'totalKelas' => Kelas::query()->count('*'),
                'totalSiswa' => $totalSiswa,
                'totalPembimbing' => Pembimbing::query()->count('*'),
                'totalBengkel' => Bengkel::query()->count('*'),
                'totalMutasi' => MutasiPKL::query()->count('*'),
                'mutasiPending' => $mutasiPending,
                'mutasiProcessing' => $mutasiProcessing,
                'mutasiApproved' => $mutasiApproved,
                'mutasiRejected' => $mutasiRejected,
                'siswaSudahPKL' => $siswaSudahPKL,
                'siswaBelumPKL' => $totalSiswa - $siswaSudahPKL,
                'chartLabels' => ['Sudah Dapat Bengkel', 'Belum Dapat Bengkel'],
                'chartDataset' => [$siswaSudahPKL, $totalSiswa - $siswaSudahPKL],
                'mutasiChartLabels' => ['Menunggu', 'Diproses', 'Disetujui', 'Ditolak'],
                'mutasiChartDataset' => [$mutasiPending, $mutasiProcessing, $mutasiApproved, $mutasiRejected],
                'bengkelChartLabels' => ['TKR', 'TSM', 'TEI', 'Animasi', 'Kuliner', 'Lainnya'],
                'bengkelChartDataset' => [$bengkelTKR, $bengkelTSM, $bengkelTEI, $bengkelANS, $bengkelKLR, $bengkelLain],
            ];
        });

        return Inertia::render('Admin/Dashboard', [
            ...$stats,
        ]);
    }
}
