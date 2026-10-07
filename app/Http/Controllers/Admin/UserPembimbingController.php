<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\UserPembimbingImport;
use App\Models\Pembimbing;
use App\Models\PembimbingUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class UserPembimbingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PembimbingUser::with(['pembimbing.jabatanPembimbing']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
                $q->orWhereHas('pembimbing', function ($qp) use ($request) {
                    $qp->where('nama_guru', 'like', '%'.$request->search.'%');
                });
                $q->orWhereHas('pembimbing.jabatanPembimbing', function ($qj) use ($request) {
                    $qj->where('nama_jabatan', 'like', '%'.$request->search.'%');
                });
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100])) {
            $perPage = 10;
        }

        $users = $query->latest()->paginate($perPage)->withQueryString();

        $pembimbings = Pembimbing::with(['jabatanPembimbing', 'account'])
            ->orderBy('nama_guru')
            ->limit(500)
            ->get()
            ->map(function ($p) {
                $status = $p->pembimbing_user_id ? ' (Terhubung: '.($p->account->name ?? 'Unknown').')' : '';

                return [
                    'id' => $p->id,
                    'label' => $p->nama_guru.($p->jabatanPembimbing ? ' - '.$p->jabatanPembimbing->nama_jabatan : '').$status,
                    'nama_guru' => $p->nama_guru,
                    'jabatan' => $p->jabatanPembimbing->nama_jabatan ?? null,
                    'is_linked' => ! is_null($p->pembimbing_user_id),
                    'linked_user_name' => $p->account->name ?? null,
                ];
            });

        return Inertia::render('Admin/UserPembimbing/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'per_page']),
            'pembimbingOptions' => $pembimbings,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pembimbing_users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'pembimbing_id' => 'nullable|exists:pembimbings,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $baseUsername = Str::slug($request->name);
                $username = $baseUsername;
                $suffix = 1;
                while (PembimbingUser::where('username', $username)->exists()) {
                    $username = $baseUsername.'-'.$suffix++;
                }

                $user = PembimbingUser::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'username' => $username,
                    'password' => $request->password,
                    'email_verified_at' => now(),
                ]);

                $sysUsername = $username;
                $suffix2 = 1;
                while (User::where('username', $sysUsername)->exists()) {
                    $sysUsername = $username.'-'.$suffix2++;
                }
                $systemUser = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'username' => $sysUsername,
                    'password' => $request->password,
                ]);
                $systemUser->assignRole('pembimbing');

                // Auto-link to selected Pembimbing
                if ($request->filled('pembimbing_id')) {
                    $p = Pembimbing::find($request->pembimbing_id);
                    if ($p) {
                        $p->update([
                            'pembimbing_user_id' => $user->id,
                            'user_id' => $systemUser->id,
                        ]);
                    }
                }
            });

            Cache::forget('admin_dashboard_stats');

            return redirect()->route('admin.users.pembimbing.index')->with('message', 'User Pembimbing created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create User Pembimbing: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = PembimbingUser::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pembimbing_users,email,'.$user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $user->update([
                'password' => $request->password,
            ]);
        }

        // Keep Pembimbing name in sync if linked
        if ($user->pembimbing) {
            $user->pembimbing->update([
                'nama_guru' => $user->name,
            ]);
            $p = $user->pembimbing;
            if ($p->user) {
                $payload = [
                    'name' => $request->name,
                    'email' => $request->email,
                ];
                if ($request->filled('password')) {
                    $payload['password'] = $request->password;
                }
                $p->user->update($payload);
                $p->user->assignRole('pembimbing');
            } else {
                $baseUsername = Str::slug($request->name);
                $sysUsername = $baseUsername;
                $suffix = 1;
                while (User::where('username', $sysUsername)->exists()) {
                    $sysUsername = $baseUsername.'-'.$suffix++;
                }
                $systemUser = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'username' => $sysUsername,
                    'password' => $request->filled('password') ? $request->password : 'password',
                ]);
                $systemUser->assignRole('pembimbing');
                $p->update(['user_id' => $systemUser->id]);
            }
        }

        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('message', 'User Pembimbing updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = PembimbingUser::findOrFail($id);

        // Unlink from Pembimbing if exists
        if ($user->pembimbing) {
            $p = $user->pembimbing;
            $p->update(['pembimbing_user_id' => null]);
            if ($p->user) {
                $p->user->delete();
            }
        }

        $user->delete();

        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('message', 'User Pembimbing deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $users = PembimbingUser::whereIn('id', $ids)->get();

        foreach ($users as $user) {
            if ($user->pembimbing) {
                $p = $user->pembimbing;
                $p->update(['pembimbing_user_id' => null]);
                if ($p->user) {
                    $p->user->delete();
                }
            }
            $user->delete();
        }

        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('message', count($users).' User Pembimbing berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new UserPembimbingImport, $request->file('file'));

            Cache::forget('admin_dashboard_stats');

            return redirect()->back()->with('message', 'User Pembimbing imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: '.$e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $rows = Pembimbing::with(['jabatanPembimbing', 'account', 'user'])
            ->orderBy('nama_guru')
            ->get()
            ->map(function ($pembimbing, $index) {
                return [
                    'No' => $index + 1,
                    'Pembimbing ID' => $pembimbing->id,
                    'Nama' => $pembimbing->nama_guru,
                    'Email' => $pembimbing->account->email ?? $pembimbing->user->email ?? '',
                    'Password' => '',
                    'Jabatan' => $pembimbing->jabatanPembimbing->nama_jabatan ?? '',
                ];
            });

        if ($rows->isEmpty()) {
            $rows = collect([
                [
                    'No' => 1,
                    'Nama' => 'Budi Santoso',
                    'Email' => 'budi@sekolah.com',
                    'Password' => 'password123',
                    'Jabatan' => 'Kaproli',
                ],
            ]);
        }

        $export = new class($rows) implements FromCollection, WithHeadings
        {
            private Collection $rows;

            public function __construct(Collection $rows)
            {
                $this->rows = $rows;
            }

            public function headings(): array
            {
                return [
                    'No',
                    'Pembimbing ID',
                    'Nama',
                    'Email',
                    'Password',
                    'Jabatan',
                ];
            }

            public function collection(): Collection
            {
                return $this->rows;
            }
        };

        return Excel::download($export, 'template_user_pembimbing.xlsx');
    }

    public function resetPassword(Request $request, string $id)
    {
        $request->validate([
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        /** @var PembimbingUser $user */
        $user = PembimbingUser::with('pembimbing.user')->findOrFail($id);

        DB::transaction(function () use ($request, $user) {
            $user->update([
                'password' => $request->password,
            ]);

            if ($user->pembimbing && $user->pembimbing->user) {
                $user->pembimbing->user->update([
                    'password' => $request->password,
                ]);
            }
        });

        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('message', "Password untuk pembimbing {$user->name} berhasil di-reset.");
    }

    public function bulkResetPassword(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:pembimbing_users,id',
            'password' => 'nullable|string|min:6',
        ]);

        $newPassword = $request->input('password') ?: 'password';

        DB::transaction(function () use ($request, $newPassword) {
            $users = PembimbingUser::query()->with('pembimbing.user')->whereIn('id', $request->ids)->get();
            $users->each(function (PembimbingUser $user) use ($newPassword) {
                $user->update([
                    'password' => $newPassword,
                ]);

                if ($user->pembimbing && $user->pembimbing->user) {
                    $user->pembimbing->user->update([
                        'password' => $newPassword,
                    ]);
                }
            });
        });

        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('message', count($request->ids).' akun user pembimbing berhasil di-reset passwordnya ke: '.$newPassword);
    }
}
