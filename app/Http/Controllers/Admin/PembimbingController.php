<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JabatanPembimbing;
use App\Models\Pembimbing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PembimbingController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembimbing::with([
            'jabatanPembimbing:id,nama_jabatan',
            'user:id,name,email',
            'account:id,name,email',
        ]);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_guru', 'like', '%'.$search.'%')
                    ->orWhere('nip', 'like', '%'.$search.'%')
                    ->orWhere('no_hp', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('email', 'like', '%'.$search.'%')
                            ->orWhere('username', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($request->has('jabatan_pembimbing_id') && $request->jabatan_pembimbing_id) {
            $query->where('jabatan_pembimbing_id', $request->jabatan_pembimbing_id);
        }

        $perPage = $request->input('per_page', 10);
        $validPerPage = [10, 20, 50, 100];
        if (! in_array((int) $perPage, $validPerPage)) {
            $perPage = 10;
        }

        $pembimbings = $query->latest()->paginate($perPage)->withQueryString();

        $jabatanOptions = Cache::remember('options.jabatan_pembimbing', 86400, function () {
            return JabatanPembimbing::select('id', 'nama_jabatan')
                ->orderBy('nama_jabatan')
                ->get();
        });

        return Inertia::render('Admin/Pembimbing/Index', [
            'pembimbings' => $pembimbings,
            'jabatanOptions' => $jabatanOptions,
            'filters' => $request->only(['search', 'jabatan_pembimbing_id', 'per_page']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_guru' => 'required|string|max:255',
            'jabatan_pembimbing_id' => 'required|exists:jabatan_pembimbings,id',
            'nip' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|in:L,P',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated) {
            $email = Str::slug($validated['nama_guru']).'@pembimbing.com';
            $count = User::where('email', 'like', $email.'%')->count();
            if ($count > 0) {
                $email = Str::slug($validated['nama_guru']).($count + 1).'@pembimbing.com';
            }

            $baseUsername = Str::slug($validated['nama_guru']);
            $username = $baseUsername;
            $suffix = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername.'-'.$suffix++;
            }

            $user = User::create([
                'name' => $validated['nama_guru'],
                'email' => $email,
                'username' => $username,
                'password' => 'password',
                'email_verified_at' => now(),
            ]);

            $user->assignRole('pembimbing');

            Cache::forget('options.pembimbing_labels');
            Cache::forget('options.jabatan_pembimbing');

            Pembimbing::create([
                'user_id' => $user->id,
                'nama_guru' => $validated['nama_guru'],
                'jabatan_pembimbing_id' => $validated['jabatan_pembimbing_id'],
                'nip' => $validated['nip'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'] ?? 'L',
                'no_hp' => $validated['no_hp'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
            ]);
        });

        return redirect()->back()->with('message', 'Pembimbing berhasil ditambahkan (User dibuat dengan password default: password).');
    }

    public function update(Request $request, Pembimbing $pembimbing)
    {
        $validated = $request->validate([
            'nama_guru' => 'required|string|max:255',
            'jabatan_pembimbing_id' => 'required|exists:jabatan_pembimbings,id',
            'nip' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|in:L,P',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($pembimbing, $validated) {
            $pembimbing->update($validated);

            if ($pembimbing->user) {
                $pembimbing->user->update([
                    'name' => $validated['nama_guru'],
                ]);
            }
        });

        Cache::forget('options.pembimbing_labels');

        return redirect()->back()->with('message', 'Pembimbing berhasil diperbarui.');
    }

    public function destroy(Pembimbing $pembimbing)
    {
        if ($pembimbing->monitorings()->exists() || $pembimbing->penempatan_pkl()->exists()) {
            return redirect()->back()->with('error', 'Guru Pembimbing tidak dapat dihapus karena masih memiliki riwayat monitoring atau penempatan siswa. Silakan pindahkan penempatan atau kelola riwayat monitoring terlebih dahulu.');
        }

        DB::transaction(function () use ($pembimbing) {
            $user = $pembimbing->user;
            $pembimbing->delete();
            if ($user) {
                $user->delete();
            }
        });

        Cache::forget('options.pembimbing_labels');
        Cache::forget('options.pembimbing_monitoring');

        return redirect()->back()->with('message', 'Pembimbing berhasil dihapus.');
    }
}
