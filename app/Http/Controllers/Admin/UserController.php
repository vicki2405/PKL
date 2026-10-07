<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->select(['id', 'name', 'email', 'username', 'created_at'])
            ->with(['roles:id,name'])
            ->whereHas('roles', function ($q) {
                $q->whereNotIn('name', ['siswa', 'pembimbing']);
            });

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 20, 50, 100, 250, 500], true)) {
            $perPage = 10;
        }

        $users = $query->latest()->paginate($perPage)->withQueryString()->through(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->map(fn ($r) => ['id' => $r->id, 'name' => $r->name]),
            ];
        });
        $roles = Role::whereNotIn('name', ['siswa', 'pembimbing'])->get();

        return Inertia::render('Admin/User/Index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => 'array',
        ]);

        try {
            $username = explode('@', $validated['email'])[0];
            $originalUsername = $username;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $originalUsername.$counter;
                $counter++;
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $username,
                'password' => $validated['password'],
            ]);

            $selectedRoles = (array) ($request->roles ?? []);
            if (empty($selectedRoles)) {
                if (! Role::where('name', 'admin')->exists()) {
                    Role::create(['name' => 'admin']);
                }
                $selectedRoles = ['admin'];
            }
            $user->syncRoles($selectedRoles);

            return redirect()->back()->with('message', 'User created successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to create user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except('password', 'password_confirmation'),
            ]);

            return redirect()->back()->with('error', 'Gagal membuat user: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
                'roles' => 'array',
            ]);

            $emailChanged = strtolower($validated['email']) !== strtolower($user->email);
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if ($emailChanged) {
                $newUsernameBase = explode('@', $validated['email'])[0];
                $username = $newUsernameBase;
                $originalUsername = $username;
                $counter = 1;
                while (User::where('username', $username)->where('id', '!=', $user->id)->exists()) {
                    $username = $originalUsername.$counter;
                    $counter++;
                }
                $user->update(['username' => $username]);
            }

            if ($request->filled('password')) {
                $request->validate([
                    'password' => ['confirmed', Rules\Password::defaults()],
                ]);
                $user->update([
                    'password' => $request->password,
                ]);
            }

            if ($request->has('roles')) {
                $selectedRoles = (array) $request->roles;
                if (empty($selectedRoles)) {
                    $selectedRoles = $user->getRoleNames()->toArray();
                }
                if (empty($selectedRoles)) {
                    if (! Role::where('name', 'admin')->exists()) {
                        Role::create(['name' => 'admin']);
                    }
                    $selectedRoles = ['admin'];
                }
                $user->syncRoles($selectedRoles);
            }

            return redirect()->back()->with('message', 'User updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to update user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $id,
                'data' => $request->except('password', 'password_confirmation'),
            ]);

            return redirect()->back()->with('error', 'Gagal mengupdate user: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        if ($user->hasRole('super-admin')) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus user super-admin.');
        }

        if ((int) $user->id === (int) auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        $hasReference = DB::table('mutasi_p_k_l_s')->where('admin_id', $user->id)->exists()
            || DB::table('rekap_problems')->where('admin_id', $user->id)->exists()
            || DB::table('pembekalan_aktivasis')->where('created_by', $user->id)->exists();

        if ($hasReference) {
            return redirect()->back()->with('error', 'User tidak dapat dihapus karena masih memiliki data terkait (mutasi, rekap problem, atau pembekalan). Hapus atau pindahkan data tersebut terlebih dahulu.');
        }

        try {
            $user->delete();
        } catch (\Throwable $e) {
            Log::error('Failed to delete user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $id,
            ]);

            return redirect()->back()->with('error', 'Gagal menghapus user: '.$e->getMessage());
        }

        return redirect()->back()->with('message', 'User deleted successfully.');
    }
}
