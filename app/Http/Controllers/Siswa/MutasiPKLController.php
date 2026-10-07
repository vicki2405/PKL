<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\MutasiPKL;
use App\Models\PenempatanPKL;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class MutasiPKLController extends Controller
{
    private function getSiswa(?User $user): ?Siswa
    {
        if (! $user) {
            return null;
        }

        $siswa = $user->siswa;
        if (! $siswa) {
            $siswa = Siswa::query()->where('nisn', $user->username)->first();
            if ($siswa) {
                $siswa->update(['user_id' => $user->id]);
            }
        }

        return $siswa;
    }

    public function create()
    {
        $user = Auth::user();
        $siswa = $this->getSiswa($user);

        if (! $siswa) {
            return redirect()->route('siswa.dashboard')->with('error', 'Akun tidak terhubung dengan data siswa.');
        }

        $siswa->load('kelas.jurusan');
        $placement = PenempatanPKL::query()->where('siswa_id', $siswa->id)->with(['bengkel', 'pembimbing'])->first();
        $bengkelOptions = \App\Models\Bengkel::query()->select(['id', 'nama_bengkel', 'kode_bengkel', 'dusun', 'desa', 'kecamatan'])->orderBy('nama_bengkel')->get();

        return Inertia::render('Siswa/MutasiPKL/Create', [
            'siswa' => [
                'id' => $siswa->id,
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas ? [
                    'nama_kelas' => $siswa->kelas->nama_kelas,
                    'jurusan' => $siswa->kelas->jurusan ? [
                        'nama_jurusan' => $siswa->kelas->jurusan->nama_jurusan,
                    ] : null,
                ] : null,
                'current_bengkel' => $placement && $placement->bengkel ? [
                    'id' => $placement->bengkel->id,
                    'nama_bengkel' => $placement->bengkel->nama_bengkel,
                    'kode_bengkel' => $placement->bengkel->kode_bengkel,
                ] : null,
                'current_pembimbing' => $placement && $placement->pembimbing ? [
                    'id' => $placement->pembimbing->id,
                    'nama_guru' => $placement->pembimbing->nama_guru,
                ] : null,
            ],
            'bengkelOptions' => $bengkelOptions,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $siswa = $this->getSiswa($user);

        if (! $siswa) {
            return back()->withErrors(['siswa' => 'Akun tidak terhubung dengan data siswa.']);
        }

        $validated = $request->validate([
            'bengkel_mode' => 'nullable|in:existing,new',
            'bengkel_tujuan_id' => 'nullable|exists:bengkels,id',
            'nama_bengkel_baru' => 'required_if:bengkel_mode,new|nullable|string|max:255',
            'nama_pemilik_bengkel_baru' => 'nullable|string|max:255',
            'dusun' => 'nullable|string|max:255',
            'desa' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'google_maps_link_baru' => 'nullable|url',
            'alasan' => 'required|string',
        ]);

        $placement = PenempatanPKL::query()->where('siswa_id', $siswa->id)->first();
        if (! $placement) {
            return back()->withErrors(['alasan' => 'Anda belum memiliki penempatan PKL saat ini.']);
        }

        $bengkelMode = $validated['bengkel_mode'] ?? ($request->filled('bengkel_tujuan_id') ? 'existing' : 'new');
        $bengkelTujuanId = null;
        $namaBengkelBaru = null;

        if ($bengkelMode === 'existing' && ! empty($validated['bengkel_tujuan_id'])) {
            $bengkelTujuanId = $validated['bengkel_tujuan_id'];
            $existingBengkel = \App\Models\Bengkel::query()->find($bengkelTujuanId);
            $namaBengkelBaru = $existingBengkel ? $existingBengkel->nama_bengkel : null;
        } else {
            $namaBengkelBaru = $validated['nama_bengkel_baru'];
        }

        MutasiPKL::create([
            'siswa_id' => $siswa->id,
            'bengkel_asal_id' => $placement->bengkel_id,
            'bengkel_tujuan_id' => $bengkelTujuanId,
            'nama_bengkel_baru' => $namaBengkelBaru,
            'nama_pemilik_bengkel_baru' => $validated['nama_pemilik_bengkel_baru'] ?? null,
            'dusun' => $validated['dusun'] ?? '',
            'desa' => $validated['desa'] ?? '',
            'kecamatan' => $validated['kecamatan'] ?? '',
            'deskripsi' => $validated['deskripsi'] ?? null,
            'google_maps_link_baru' => $validated['google_maps_link_baru'] ?? null,
            'pembimbing_id' => $placement->pembimbing_id,
            'alasan' => $validated['alasan'],
            'status' => 'pending',
        ]);

        Cache::forget('mutasi_pending_count');
        Cache::forget('admin_dashboard_stats');

        return redirect()->route('siswa.dashboard')->with('message', 'Pengajuan mutasi berhasil dikirim.');
    }
}
