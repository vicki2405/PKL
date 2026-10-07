<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Informasi;
use App\Models\MutasiPKL;
use App\Models\PenempatanPKL;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    private function getPenempatanData(int $siswaId): ?array
    {
        $penempatanModel = PenempatanPKL::query()
            ->where('siswa_id', $siswaId)
            ->select(['id', 'siswa_id', 'bengkel_id', 'pembimbing_id', 'created_at'])
            ->with([
                'bengkel:id,nama_bengkel,nama_pemilik,dusun,desa,kecamatan,google_maps_link,no_hp,deskripsi',
                'bengkel.penempatan_pkl:id,bengkel_id,siswa_id',
                'bengkel.penempatan_pkl.siswa:id,nama,kelas_id',
                'bengkel.penempatan_pkl.siswa.kelas:id,nama_kelas',
                'pembimbing:id,nama_guru,no_hp',
            ])
            ->latest('created_at')
            ->first();

        if (! $penempatanModel) {
            return null;
        }

        $bengkel = $penempatanModel->bengkel;
        $rekanList = [];
        if ($bengkel && $bengkel->penempatan_pkl) {
            foreach ($bengkel->penempatan_pkl as $p) {
                if (! $p->siswa) {
                    continue;
                }
                $rekanList[] = [
                    'id' => $p->id,
                    'nama' => $p->siswa->nama,
                    'kelas' => $p->siswa->kelas?->nama_kelas,
                    'is_kamu' => $p->siswa_id === $siswaId,
                ];
            }
            usort($rekanList, fn ($a, $b) => ($b['is_kamu'] ? 1 : 0) <=> ($a['is_kamu'] ? 1 : 0));
        }

        $alamatBengkel = collect([$bengkel?->dusun, $bengkel?->desa, $bengkel?->kecamatan])->filter()->join(', ');

        return [
            'id' => $penempatanModel->id,
            'bengkel' => $bengkel ? [
                'id' => $bengkel->id,
                'nama_bengkel' => $bengkel->nama_bengkel,
                'nama_pemilik' => $bengkel->nama_pemilik,
                'dusun' => $bengkel->dusun,
                'desa' => $bengkel->desa,
                'kecamatan' => $bengkel->kecamatan,
                'alamat_lengkap' => $alamatBengkel ?: null,
                'google_maps_link' => $bengkel->google_maps_link,
                'no_hp' => $bengkel->no_hp,
                'deskripsi' => $bengkel->deskripsi,
            ] : null,
            'pembimbing' => $penempatanModel->pembimbing ? [
                'id' => $penempatanModel->pembimbing->id,
                'nama_guru' => $penempatanModel->pembimbing->nama_guru,
                'no_hp' => $penempatanModel->pembimbing->no_hp,
            ] : null,
            'rekan_magang' => $rekanList,
        ];
    }

    private function getMutasiData(int $siswaId): ?array
    {
        $mutasiModel = MutasiPKL::query()
            ->where('siswa_id', $siswaId)
            ->select([
                'id',
                'siswa_id',
                'bengkel_asal_id',
                'bengkel_tujuan_id',
                'nama_bengkel_baru',
                'nama_pemilik_bengkel_baru',
                'dusun',
                'desa',
                'kecamatan',
                'deskripsi',
                'google_maps_link_baru',
                'alasan',
                'status',
                'admin_note',
                'created_at',
            ])
            ->with([
                'bengkelTujuan:id,nama_bengkel,nama_pemilik,dusun,desa,kecamatan,google_maps_link,no_hp',
            ])
            ->latest('created_at')
            ->first();

        if (! $mutasiModel) {
            return null;
        }

        $namaBengkel = $mutasiModel->bengkelTujuan?->nama_bengkel ?? $mutasiModel->nama_bengkel_baru;
        $dusun = $mutasiModel->dusun ?: $mutasiModel->bengkelTujuan?->dusun;
        $desa = $mutasiModel->desa ?: $mutasiModel->bengkelTujuan?->desa;
        $kecamatan = $mutasiModel->kecamatan ?: $mutasiModel->bengkelTujuan?->kecamatan;
        $googleMaps = $mutasiModel->google_maps_link_baru ?: $mutasiModel->bengkelTujuan?->google_maps_link;

        $alamat = collect([$dusun, $desa, $kecamatan])->filter()->unique()->join(', ');

        return [
            'id' => $mutasiModel->id,
            'status' => $mutasiModel->status,
            'nama_bengkel_baru' => $namaBengkel,
            'nama_pemilik_bengkel_baru' => $mutasiModel->nama_pemilik_bengkel_baru ?? $mutasiModel->bengkelTujuan?->nama_pemilik,
            'alamat_bengkel_baru' => $alamat ?: null,
            'google_maps_link_baru' => $googleMaps,
            'alasan' => $mutasiModel->alasan,
            'admin_note' => $mutasiModel->admin_note,
            'created_at' => $mutasiModel->created_at,
        ];
    }

    /**
     * @return \Inertia\Response
     */
    public function index(): \Inertia\Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $siswa = $user?->siswa;
        if (! $siswa && $user) {
            $siswa = Siswa::query()->where('nisn', $user->username)->first();
            if ($siswa) {
                $siswa->update(['user_id' => $user->id]);
            }
        }

        if ($siswa) {
            $siswa->loadMissing(['kelas.jurusan', 'karir']);
        }

        $penempatan = $siswa ? $this->getPenempatanData($siswa->id) : null;
        $mutasi = $siswa ? $this->getMutasiData($siswa->id) : null;

        $siswaProfile = $siswa ? [
            'id' => $siswa->id,
            'nama' => $siswa->nama,
            'nisn' => $siswa->nisn,
            'nipd' => $siswa->nipd,
            'no_hp' => $siswa->no_hp,
            'kelas' => $siswa->kelas?->nama_kelas,
            'jurusan' => $siswa->kelas?->jurusan?->nama_jurusan,
            'kode_jurusan' => $siswa->kelas?->jurusan?->kode_jurusan,
        ] : null;

        $karirStatus = [
            'has_karir' => (bool) $siswa?->karir,
            'tipe_karir' => $siswa?->karir?->tipe_karir,
            'can_edit' => is_null($siswa?->karir) || $siswa?->karir?->edit_count < 1,
        ];

        $informasis = Cache::remember('informasis:active:latest5', 300, function () {
            return Informasi::query()
                ->where('status', true)
                ->select(['id', 'judul', 'deskripsi', 'tanggal_terbit'])
                ->latest('tanggal_terbit')
                ->limit(5)
                ->get();
        });

        return Inertia::render('Siswa/Dashboard', [
            'siswaProfile' => $siswaProfile,
            'karirStatus' => $karirStatus,
            'penempatan' => $penempatan,
            'mutasi' => $mutasi,
            'informasis' => $informasis,
        ]);
    }
}

