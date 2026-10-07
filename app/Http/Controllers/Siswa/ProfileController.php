<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Support\AuthorizationCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProfileController extends Controller
{
    public function photo(Request $request)
    {
        $user = Auth::user();
        $siswa = $user ? $user->siswa : null;

        if (! $siswa) {
            abort(404);
        }

        $media = $siswa->getFirstMedia('profile_photos');
        if (! $media) {
            abort(404);
        }

        $path = $media->getPath();
        if (! $path || ! is_file($path)) {
            abort(404);
        }

        $hasVersion = $request->query('v') !== null;

        return response()->file($path, [
            'Cache-Control' => $hasVersion ? 'public, max-age=86400, immutable' : 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function edit()
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        $siswaData = null;

        if ($siswa) {
            $siswa->load('kelas.jurusan');
            $media = $siswa->getFirstMedia('profile_photos');
            $hasPhoto = $media && $media->getPath() && is_file($media->getPath());
            $siswaData = [
                'id' => $siswa->id,
                'nisn' => $siswa->nisn,
                'nipd' => $siswa->nipd,
                'nama' => $siswa->nama,
                'tempat_lahir' => $siswa->tempat_lahir,
                'tanggal_lahir' => $siswa->tanggal_lahir, // MySQL date is usually Y-m-d, safe for input
                'jenis_kelamin' => $siswa->jenis_kelamin,
                'agama' => $siswa->agama,
                'no_hp' => $siswa->no_hp,
                'alamat' => $siswa->alamat,
                'rt' => $siswa->rt,
                'rw' => $siswa->rw,
                'dusun' => $siswa->dusun,
                'kelurahan' => $siswa->kelurahan,
                'kecamatan' => $siswa->kecamatan,
                'profile_photo_url' => $hasPhoto ? route('siswa.profile.photo', ['v' => $media->updated_at?->timestamp]) : null,
                'kelas' => $siswa->kelas ? [
                    'nama_kelas' => $siswa->kelas->nama_kelas,
                    'jurusan' => $siswa->kelas->jurusan ? [
                        'kode_jurusan' => $siswa->kelas->jurusan->kode_jurusan,
                        'nama_jurusan' => $siswa->kelas->jurusan->nama_jurusan,
                    ] : null,
                ] : null,
            ];
        }

        return Inertia::render('Siswa/Profile', [
            'mustVerifyEmail' => $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail,
            'status' => session('status'),
            'siswa' => $siswaData,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
                Rule::unique('siswa_users')->ignore($siswa?->siswa_user_id),
            ],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'rt' => ['nullable', 'string', 'max:10'],
            'rw' => ['nullable', 'string', 'max:10'],
            'dusun' => ['nullable', 'string', 'max:100'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $updateUser = ['email' => $request->email];
        if ($request->email !== $user->email) {
            $updateUser['email_verified_at'] = null;
        }
        \App\Models\User::whereKey($user->id)->update($updateUser);

        if ($siswa && $siswa->siswa_user_id) {
            \App\Models\SiswaUser::whereKey($siswa->siswa_user_id)->update([
                'email' => $request->email,
            ]);
        }

        if ($siswa) {
            $siswa->update([
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'rt' => $request->rt,
                'rw' => $request->rw,
                'dusun' => $request->dusun,
                'kelurahan' => $request->kelurahan,
                'kecamatan' => $request->kecamatan,
            ]);

            if ($request->hasFile('photo')) {
                $uploaded = $request->file('photo');
                $path = $uploaded->getPathname();

                $imageInfo = @getimagesize($path);
                if ($imageInfo === false || empty($imageInfo[0]) || empty($imageInfo[1])) {
                    throw ValidationException::withMessages([
                        'photo' => ['File foto tidak valid.'],
                    ]);
                }

                $width = (int) $imageInfo[0];
                $height = (int) $imageInfo[1];

                if ($width <= 0 || $height <= 0 || ($width * $height) > 60000000 || $width > 12000 || $height > 12000) {
                    throw ValidationException::withMessages([
                        'photo' => ['Resolusi foto terlalu besar.'],
                    ]);
                }

                $manager = new ImageManager(new Driver);
                $image = $manager->read($path)->scaleDown(width: 1280);

                $tmpDir = 'tmp/profile-photos';
                Storage::disk('local')->makeDirectory($tmpDir);
                $tmpRelativePath = $tmpDir.'/'.Str::uuid()->toString().'.jpg';
                $tmpAbsolutePath = Storage::disk('local')->path($tmpRelativePath);

                $image->toJpeg(quality: 75)->save($tmpAbsolutePath);

                $siswa->clearMediaCollection('profile_photos');

                $siswa->addMedia($tmpAbsolutePath)
                    ->usingFileName('profile_'.$user->id.'.jpg')
                    ->toMediaCollection('profile_photos');

                Storage::disk('local')->delete($tmpRelativePath);
                $request->session()->forget('profile_photo_url');
                Cache::forget(AuthorizationCache::profilePhotoKey($user->id));
                AuthorizationCache::bump();
            }
        }

        $request->session()->forget('profile_photo_url');

        return redirect()->route('siswa.profile')->with('message', 'Profil berhasil diperbarui.');
    }
}
