<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Pembimbing;
use App\Models\PenempatanPKL;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * @return \Inertia\Response
     */
    public function index(): \Inertia\Response
    {
        /** @var User|null $user */
        $user = Auth::user();
        $pembimbing = Pembimbing::query()
            ->where('user_id', $user->id)
            ->select(['id', 'user_id', 'nama_guru'])
            ->first();

        if (! $pembimbing) {
            $pembimbing = Pembimbing::query()
                ->where('nama_guru', $user->name)
                ->first();

            if ($pembimbing && ! $pembimbing->user_id) {
                $pembimbing->update(['user_id' => $user->id]);
            }
        }

        $stats = [
            'total_siswa' => 0,
            'total_bengkel' => 0,
            'bimbingan_pending' => 0,
            'bengkel_data' => [],
        ];

        if ($pembimbing) {
            $stats = Cache::remember("pembimbing:{$pembimbing->id}:dashboard_stats", 300, function () use ($pembimbing) {
                $penempatan = PenempatanPKL::query()
                    ->where('pembimbing_id', $pembimbing->id)
                    ->select(['id', 'bengkel_id', 'siswa_id'])
                    ->with([
                        'siswa:id,nama,kelas_id,no_hp',
                        'siswa.kelas:id,nama_kelas,jurusan_id,wali_kelas,no_wa_wali',
                        'siswa.kelas.jurusan:id,nama_jurusan',
                        'bengkel:id,nama_bengkel,dusun,desa,kecamatan,google_maps_link',
                    ])
                    ->get();

                $bengkelGroups = $penempatan->groupBy('bengkel_id');

                $bengkelData = $bengkelGroups->map(function ($group) {
                    $bengkel = $group->first()->bengkel;
                    if (! $bengkel) {
                        return null;
                    }

                    $students = $group->map(function ($p) {
                        $siswa = $p->siswa;

                        return $siswa ? [
                            'id' => $siswa->id,
                            'nama' => $siswa->nama,
                            'kelas' => $siswa->kelas ? $siswa->kelas->nama_kelas : '-',
                            'jurusan' => $siswa->kelas && $siswa->kelas->jurusan ? $siswa->kelas->jurusan->nama_jurusan : '-',
                            'wali_kelas' => $siswa->kelas ? $siswa->kelas->wali_kelas : '-',
                            'no_wa_wali' => $siswa->kelas ? $siswa->kelas->no_wa_wali : '-',
                            'no_hp' => $siswa->no_hp ?? '-',
                        ] : null;
                    })->filter()->values();

                    $alamat = collect([$bengkel->dusun, $bengkel->desa, $bengkel->kecamatan])->filter()->join(', ');

                    return [
                        'id' => $bengkel->id,
                        'nama_bengkel' => $bengkel->nama_bengkel,
                        'alamat' => $alamat,
                        'google_maps_link' => $bengkel->google_maps_link,
                        'siswa_count' => $students->count(),
                        'siswas' => $students,
                    ];
                })->filter()->values();

                return [
                    'total_siswa' => $bengkelData->sum('siswa_count'),
                    'total_bengkel' => $bengkelData->count(),
                    'bimbingan_pending' => 0,
                    'bengkel_data' => $bengkelData,
                ];
            });
        }

        return Inertia::render('Pembimbing/Dashboard', [
            'stats' => $stats,
            'pembimbing' => $pembimbing ? [
                'id' => $pembimbing->id,
                'nama' => $pembimbing->nama_guru,
                'nama_guru' => $pembimbing->nama_guru,
            ] : null,
        ]);
    }
}
