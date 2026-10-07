<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UserSiswaTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\UserSiswaImport;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class UserSiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SiswaUser::query()
            ->select(['id', 'name', 'username', 'email', 'created_at'])
            ->with([
                'siswa:id,siswa_user_id,kelas_id,nama,nisn',
                'siswa.kelas:id,jurusan_id,nama_kelas',
                'siswa.kelas.jurusan:id,nama_jurusan,kode_jurusan',
            ]);

        if ($request->filled('jurusan_id')) {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('jurusan_id', $request->jurusan_id);
            });
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('username', 'like', '%'.$search.'%')
                    ->orWhereHas('siswa', function ($sq) use ($search) {
                        $sq->where('nisn', 'like', '%'.$search.'%')
                            ->orWhere('nama', 'like', '%'.$search.'%')
                            ->orWhereHas('kelas', function ($kq) use ($search) {
                                $kq->where('nama_kelas', 'like', '%'.$search.'%');
                            });
                    });
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100, 250, 500], true)) {
            $perPage = 20;
        }

        $sortBy = $request->input('sort_by', 'name');
        $direction = strtolower((string) $request->input('direction', 'asc'));

        if (! in_array($sortBy, ['name', 'username', 'created_at'], true)) {
            $sortBy = 'name';
        }
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $users = $query->orderBy($sortBy, $direction)->paginate($perPage)->withQueryString();

        $jurusans = Jurusan::query()->select(['id', 'nama_jurusan', 'kode_jurusan'])->orderBy('nama_jurusan')->get();
        $kelas = Kelas::query()->select(['id', 'jurusan_id', 'nama_kelas'])->orderBy('nama_kelas')->get();

        $stats = [
            'total_users' => SiswaUser::count(),
            'total_linked' => Siswa::whereNotNull('siswa_user_id')->count(),
            'total_unlinked' => Siswa::whereNull('siswa_user_id')->where('user_account_enabled', '!=', false)->count(),
            'total_kelas' => Kelas::count(),
        ];

        return Inertia::render('Admin/UserSiswa/Index', [
            'users' => $users,
            'jurusans' => $jurusans,
            'kelas' => $kelas,
            'stats' => $stats,
            'unlinkedSiswas' => [],
            'filters' => $request->only(['search', 'jurusan_id', 'kelas_id', 'per_page', 'sort_by', 'direction']),
        ]);
    }

    public function candidates(Request $request)
    {
        $kelasId = (int) $request->input('kelas_id');
        $search = trim((string) $request->input('search', ''));

        if ($kelasId <= 0) {
            return response()->json([
                'data' => [],
            ]);
        }

        $query = Siswa::query()
            ->with('kelas:id,nama_kelas')
            ->select('id', 'nama', 'kelas_id', 'nisn', 'siswa_user_id', 'user_account_enabled')
            ->where('kelas_id', $kelasId)
            ->orderBy('nama')
            ->limit(300);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('nisn', 'like', '%'.$search.'%');
            });
        }

        return response()->json([
            'data' => $query->get()->map(function (Siswa $siswa) {
                $hasActiveAccount = $siswa->siswa_user_id && SiswaUser::query()->where('id', $siswa->siswa_user_id)->exists();

                return [
                    'id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'kelas_id' => $siswa->kelas_id,
                    'nisn' => $siswa->nisn,
                    'siswa_user_id' => $hasActiveAccount ? $siswa->siswa_user_id : null,
                    'user_account_enabled' => $siswa->user_account_enabled,
                    'kelas' => $siswa->kelas ? [
                        'id' => $siswa->kelas->id,
                        'nama_kelas' => $siswa->kelas->nama_kelas,
                    ] : null,
                ];
            })->values(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:siswa_users,username',
            'password' => ['nullable', 'confirmed', 'min:6'],
            'kelas_id' => 'required|exists:kelas,id',
            'siswa_id' => 'nullable|exists:siswas,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $rawUsername = trim((string) $request->username);
                $rawName = trim((string) $request->name);
                $password = $request->filled('password') ? $request->password : 'password';

                $email = $rawUsername.'@siswa.sekolah.com';
                if (SiswaUser::query()->where('email', $email)->exists()) {
                    $email = null;
                }

                $user = SiswaUser::query()->create([
                    'name' => $rawName,
                    'username' => $rawUsername,
                    'email' => $email,
                    'password' => $password,
                    'plain_password' => $password,
                    'email_verified_at' => now(),
                ]);

                $baseUsername = $rawUsername;
                $sysUsername = $baseUsername;
                $suffix = 1;
                while (User::query()->where('username', $sysUsername)->exists()) {
                    $sysUsername = $baseUsername.'-'.$suffix++;
                }
                $sysEmail = $email;
                if ($sysEmail && User::query()->where('email', $sysEmail)->exists()) {
                    $sysEmail = null;
                }
                $systemUser = User::query()->create([
                    'name' => $rawName,
                    'username' => $sysUsername,
                    'email' => $sysEmail,
                    'password' => $password,
                ]);
                $systemUser->assignRole('siswa');

                // Check existing Siswa
                $siswa = null;
                if ($request->siswa_id) {
                    $siswa = Siswa::query()->find($request->siswa_id);
                }
                if (! $siswa) {
                    $siswa = Siswa::query()->where('nisn', $rawUsername)->first();
                }

                if ($siswa) {
                    $siswa->update([
                        'siswa_user_id' => $user->id,
                        'user_id' => $systemUser->id,
                        'user_account_enabled' => true,
                        'kelas_id' => $request->kelas_id ?: $siswa->kelas_id,
                        'nama' => $rawName,
                    ]);
                    $user->update(['name' => $siswa->nama]);
                    $systemUser->update(['name' => $siswa->nama]);
                } else {
                    Siswa::query()->create([
                        'siswa_user_id' => $user->id,
                        'user_account_enabled' => true,
                        'kelas_id' => $request->kelas_id,
                        'nama' => $rawName,
                        'nisn' => $rawUsername,
                        'user_id' => $systemUser->id,
                    ]);
                }
            });

            Cache::forget('admin_dashboard_stats');

            return redirect()->route('admin.users.siswa.index')->with('message', 'User Siswa berhasil ditambahkan.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error creating User Siswa: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal menambahkan User Siswa: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        /** @var SiswaUser $user */
        $user = SiswaUser::query()->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:siswa_users,username,'.$user->id,
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        try {
            DB::transaction(function () use ($request, $user) {
                $rawName = trim((string) $request->name);
                $rawUsername = trim((string) $request->username);

                $user->update([
                    'name' => $rawName,
                    'username' => $rawUsername,
                ]);

                if ($request->filled('password')) {
                    $request->validate([
                        'password' => ['confirmed', 'min:3'],
                    ]);
                    $user->update([
                        'password' => $request->password,
                        'plain_password' => $request->password,
                    ]);
                }

                // Update or link Siswa
                $siswa = $user->siswa;
                if ($siswa) {
                    $siswa->update([
                        'kelas_id' => $request->kelas_id,
                        'nama' => $rawName,
                        'nisn' => $rawUsername,
                        'user_account_enabled' => true,
                    ]);
                } else {
                    $existingSiswa = Siswa::query()->where('nisn', $rawUsername)->first();
                    if ($existingSiswa) {
                        $existingSiswa->update([
                            'siswa_user_id' => $user->id,
                            'user_account_enabled' => true,
                            'kelas_id' => $request->kelas_id,
                            'nama' => $rawName,
                        ]);
                        $siswa = $existingSiswa;
                    } else {
                        $siswa = Siswa::query()->create([
                            'siswa_user_id' => $user->id,
                            'user_account_enabled' => true,
                            'kelas_id' => $request->kelas_id,
                            'nama' => $rawName,
                            'nisn' => $rawUsername,
                        ]);
                    }
                }

                // Update or create linked system User
                if ($siswa && $siswa->user) {
                    $systemUser = $siswa->user;
                    $payload = [
                        'name' => $rawName,
                    ];
                    if ($systemUser->username !== $rawUsername) {
                        $sysUsername = $rawUsername;
                        $suffix = 1;
                        while (User::query()->where('username', $sysUsername)->where('id', '!=', $systemUser->id)->exists()) {
                            $sysUsername = $rawUsername.'-'.$suffix++;
                        }
                        $payload['username'] = $sysUsername;
                    }
                    if ($request->filled('password')) {
                        $payload['password'] = $request->password;
                    }
                    $systemUser->update($payload);
                    $systemUser->assignRole('siswa');
                } elseif ($siswa && ! $siswa->user) {
                    $baseUsername = $rawUsername;
                    $sysUsername = $baseUsername;
                    $suffix = 1;
                    while (User::query()->where('username', $sysUsername)->exists()) {
                        $sysUsername = $baseUsername.'-'.$suffix++;
                    }
                    $systemUser = User::query()->create([
                        'name' => $rawName,
                        'username' => $sysUsername,
                        'password' => $request->filled('password') ? $request->password : ($user->plain_password ?: 'password'),
                    ]);
                    $systemUser->assignRole('siswa');
                    $siswa->update(['user_id' => $systemUser->id]);
                }
            });

            Cache::forget('admin_dashboard_stats');

            return redirect()->route('admin.users.siswa.index')->with('message', 'User Siswa berhasil diperbarui.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error updating User Siswa: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal memperbarui User Siswa: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        /** @var SiswaUser $user */
        $user = SiswaUser::query()->findOrFail($id);
        DB::transaction(function () use ($user) {
            if ($user->siswa) {
                $siswa = $user->siswa;
                $siswa->update([
                    'user_account_enabled' => false,
                    'siswa_user_id' => null,
                ]);
                $legacyUser = $siswa->user;
                if ($legacyUser) {
                    User::destroy($legacyUser->id);
                }
            }
            SiswaUser::destroy($user->id);
        });

        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('message', 'User Siswa deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:siswa_users,id',
        ]);

        DB::transaction(function () use ($request) {
            $users = SiswaUser::query()->with('siswa.user')->whereIn('id', $request->ids)->get();
            $users->each(function (SiswaUser $user) {
                if ($user->siswa) {
                    $siswa = $user->siswa;
                    $siswa->update([
                        'user_account_enabled' => false,
                        'siswa_user_id' => null,
                    ]);
                    $legacyUser = $siswa->user;
                    if ($legacyUser) {
                        User::destroy($legacyUser->id);
                    }
                }
                SiswaUser::destroy($user->id);
            });
        });

        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('message', 'Selected User Siswa deleted successfully.');
    }

    public function bulkResetPassword(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:siswa_users,id',
            'password' => 'nullable|string|min:6',
        ]);

        $newPassword = $request->input('password') ?: 'password';

        DB::transaction(function () use ($request, $newPassword) {
            $users = SiswaUser::query()->with('siswa.user')->whereIn('id', $request->ids)->get();
            $users->each(function (SiswaUser $user) use ($newPassword) {
                $user->update([
                    'password' => $newPassword,
                    'plain_password' => $newPassword,
                ]);

                if ($user->siswa && $user->siswa->user) {
                    $user->siswa->user->update([
                        'password' => $newPassword,
                    ]);
                }
            });
        });

        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('message', count($request->ids).' akun user siswa berhasil di-reset passwordnya ke: '.$newPassword);
    }

    public function resetPassword(Request $request, string $id)
    {
        $request->validate([
            'password' => ['required', 'confirmed', 'min:3'],
        ]);

        /** @var SiswaUser $user */
        $user = SiswaUser::query()->findOrFail($id);

        DB::transaction(function () use ($request, $user) {
            $user->update([
                'password' => $request->password,
                'plain_password' => $request->password,
            ]);

            if ($user->siswa && $user->siswa->user) {
                $user->siswa->user->update([
                    'password' => $request->password,
                ]);
            }
        });

        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('message', "Password untuk {$user->name} berhasil di-reset.");
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new UserSiswaImport, $request->file('file'));

            Cache::forget('admin_dashboard_stats');

            return redirect()->back()->with('message', 'User Siswa imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: '.$e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new UserSiswaTemplateExport, 'template_user_siswa.xlsx');
    }
}
