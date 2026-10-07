<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\KarirSetting;
use App\Models\Siswa;
use App\Models\SiswaKarir;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class KarirController extends Controller
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

    public function edit()
    {
        $user = Auth::user();
        $siswa = $this->getSiswa($user);

        if (! $siswa) {
            return redirect()->route('siswa.dashboard')->with('error', 'Data siswa belum terhubung dengan akun Anda.');
        }

        $karir = SiswaKarir::where('siswa_id', $siswa->id)->first();
        $portalSetting = KarirSetting::getSetting();
        $isPortalOpen = (bool) $portalSetting->is_open;

        // Boleh mengisi jika portal dibuka DAN (belum ada atau mengubah 1x lagi)
        $canEdit = $isPortalOpen && (is_null($karir) || $karir->edit_count < 1);

        return Inertia::render('Siswa/Karir', [
            'karir' => $karir,
            'canEdit' => $canEdit,
            'isPortalOpen' => $isPortalOpen,
            'portalMessage' => $portalSetting->pesan_penutupan ?: 'Pengisian formulir rencana karier saat ini sedang ditutup oleh pihak sekolah/admin.',
            'siswa' => $siswa->only(['id', 'nama', 'nisn']),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $siswa = $this->getSiswa($user);

        if (! $siswa) {
            return redirect()->route('siswa.dashboard')->with('error', 'Data siswa belum terhubung.');
        }

        if (! KarirSetting::isOpen()) {
            $setting = KarirSetting::getSetting();
            $msg = $setting->pesan_penutupan ?: 'Pengisian formulir rencana karier sedang ditutup oleh pihak sekolah.';
            return redirect()->route('siswa.karir.edit')->with('error', $msg);
        }

        // Batas pengisian: isi 1x + boleh ubah 1x = total 2x, setelah itu terkunci
        $existing = SiswaKarir::where('siswa_id', $siswa->id)->first();

        if ($existing && $existing->edit_count >= 1) {
            return redirect()->route('siswa.karir.edit')
                ->with('message', 'Rencana karier sudah diubah satu kali dan tidak dapat diubah lagi.');
        }

        $request->validate([
            'tipe_karir' => 'required|in:kuliah,kerja,wirausaha',
        ]);

        $tipe = $request->tipe_karir;
        $rules = ['tipe_karir' => 'required'];

        if ($tipe === 'kuliah') {
            $rules = array_merge($rules, [
                'nama_universitas' => 'required|string|max:150',
                'status_universitas' => 'required|in:PTN,PTS,Kedinasan,Luar Negeri',
                'jenjang_studi' => 'required|in:D4,S1',
                'program_studi' => 'required|string|max:100',
                'jalur_masuk' => 'required|in:SNBP,SNBT,Mandiri,Beasiswa,Lainnya',
                'tahun_mulai' => 'required|integer|min:'.(now()->year - 2).'|max:'.(now()->year + 5),
            ]);
        } elseif ($tipe === 'kerja') {
            $rules = array_merge($rules, [
                'nama_perusahaan' => 'required|string|max:150',
                'posisi_kerja' => 'required|string|max:100',
                'jenis_pekerjaan' => 'required|in:Kontrak,Tetap,Magang,Freelance',
                'kesesuaian_jurusan' => 'required|in:Sangat Sesuai,Sesuai,Kurang Sesuai,Tidak Sesuai',
                'target_mulai_kerja' => 'required|in:Segera,1-3 Bulan,3-6 Bulan,Lainnya',
                'ekspektasi_gaji' => 'required|in:< UMK,Setara UMK,> UMK',
                'lokasi_kerja' => 'required|string|max:250',
            ]);
        } elseif ($tipe === 'wirausaha') {
            $rules = array_merge($rules, [
                'nama_usaha' => 'required|string|max:150',
                'sektor_usaha' => 'required|in:Jasa,Manufaktur,Kuliner,Kreatif,Perdagangan,Lainnya',
                'status_kepemilikan' => 'required|in:Mandiri,Keluarga,Kemitraan',
                'modal_awal' => 'required|in:< 5 Juta,5 - 20 Juta,> 20 Juta',
                'lokasi_usaha' => 'required|string|max:250',
            ]);
        }

        $validatedData = $request->validate($rules);

        // Reset fields of other paths to NULL to keep data clean
        $payload = array_merge([
            // Reset Kuliah
            'nama_universitas' => null, 'status_universitas' => null, 'jenjang_studi' => null,
            'program_studi' => null, 'jalur_masuk' => null, 'tahun_mulai' => null,
            // Reset Kerja
            'nama_perusahaan' => null, 'posisi_kerja' => null, 'jenis_pekerjaan' => null,
            'kesesuaian_jurusan' => null, 'target_mulai_kerja' => null, 'ekspektasi_gaji' => null,
            'lokasi_kerja' => null,
            // Reset Wirausaha
            'nama_usaha' => null, 'sektor_usaha' => null, 'status_kepemilikan' => null,
            'modal_awal' => null, 'lokasi_usaha' => null,
        ], $validatedData);

        if ($existing) {
            // Mengubah 1x (total 2x pengisian)
            $payload['edit_count'] = 1;
        }

        SiswaKarir::updateOrCreate(
            ['siswa_id' => $siswa->id],
            $payload
        );

        \App\Http\Controllers\Admin\RekapKarirController::clearPdfCache();

        $message = $existing
            ? 'Rencana karier berhasil diubah. Kesempatan mengubah sudah habis.'
            : 'Rencana karier berhasil disimpan. Anda masih memiliki 1x kesempatan untuk mengubah.';

        return redirect()->route('siswa.karir.edit')->with('message', $message);
    }
}
