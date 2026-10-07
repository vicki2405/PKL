<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel; // Add this import

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::query()
            ->select('siswas.*')
            ->with(['kelas.jurusan', 'user', 'account'])
            ->withCount([
                'media as profile_photo_count' => function ($q) {
                    $q->where('collection_name', 'profile_photos');
                },
            ]);

        $query->leftJoin('kelas', 'kelas.id', '=', 'siswas.kelas_id');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('siswas.nama', 'like', '%'.$request->search.'%')
                    ->orWhere('siswas.nisn', 'like', '%'.$request->search.'%')
                    ->orWhere('siswas.nipd', 'like', '%'.$request->search.'%')
                    ->orWhere('kelas.wali_kelas', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->kelas_id) {
            $query->where('siswas.kelas_id', $request->kelas_id);
        }

        if ($request->jurusan_id) {
            $query->where('kelas.jurusan_id', $request->jurusan_id);
        }

        if ($request->jenis_kelamin) {
            $query->where('siswas.jenis_kelamin', $request->jenis_kelamin);
        }

        $progress = $request->input('progress');
        if (is_array($progress)) {
            $progress = $progress['id'] ?? null;
        }
        if ($progress === 'complete') {
            $query
                ->whereNotNull('nisn')->where('nisn', '!=', '')
                ->whereNotNull('nipd')->where('nipd', '!=', '')
                ->whereNotNull('nama')->where('nama', '!=', '')
                ->whereNotNull('tempat_lahir')->where('tempat_lahir', '!=', '')
                ->whereNotNull('tanggal_lahir')
                ->whereNotNull('jenis_kelamin')->where('jenis_kelamin', '!=', '')
                ->whereNotNull('agama')->where('agama', '!=', '')
                ->whereNotNull('no_hp')->where('no_hp', '!=', '')
                ->whereNotNull('alamat')->where('alamat', '!=', '')
                ->whereNotNull('rt')->where('rt', '!=', '')
                ->whereNotNull('rw')->where('rw', '!=', '')
                ->whereNotNull('dusun')->where('dusun', '!=', '')
                ->whereNotNull('kelurahan')->where('kelurahan', '!=', '')
                ->whereNotNull('kecamatan')->where('kecamatan', '!=', '')
                ->whereHas('media', function ($q) {
                    $q->where('collection_name', 'profile_photos');
                })
                ->where(function ($q) {
                    $q->whereHas('user', function ($uq) {
                        $uq->whereNotNull('email')->where('email', '!=', '');
                    })->orWhereHas('account', function ($aq) {
                        $aq->whereNotNull('email')->where('email', '!=', '');
                    });
                });
        } elseif ($progress === 'below_50') {
            $query->whereRaw('
                (CASE WHEN nisn IS NOT NULL AND nisn != \'\' THEN 1 ELSE 0 END +
                 CASE WHEN nipd IS NOT NULL AND nipd != \'\' THEN 1 ELSE 0 END +
                 CASE WHEN nama IS NOT NULL AND nama != \'\' THEN 1 ELSE 0 END +
                 CASE WHEN tempat_lahir IS NOT NULL AND tempat_lahir != \'\' THEN 1 ELSE 0 END +
                 CASE WHEN tanggal_lahir IS NOT NULL THEN 1 ELSE 0 END +
                 CASE WHEN jenis_kelamin IS NOT NULL AND jenis_kelamin != \'\' THEN 1 ELSE 0 END +
                 CASE WHEN agama IS NOT NULL AND agama != \'\' THEN 1 ELSE 0 END +
                 CASE WHEN no_hp IS NOT NULL AND no_hp != \'\' THEN 1 ELSE 0 END +
                 CASE WHEN alamat IS NOT NULL AND alamat != \'\' THEN 1 ELSE 0 END +
                 CASE WHEN rt IS NOT NULL AND rt != \'\' THEN 1 ELSE 0 END +
                 CASE WHEN rw IS NOT NULL AND rw != \'\' THEN 1 ELSE 0 END +
                 CASE WHEN dusun IS NOT NULL AND dusun != \'\' THEN 1 ELSE 0 END +
                 CASE WHEN kelurahan IS NOT NULL AND kelurahan != \'\' THEN 1 ELSE 0 END +
                 CASE WHEN kecamatan IS NOT NULL AND kecamatan != \'\' THEN 1 ELSE 0 END)
                <= 7
            ');
        } elseif ($progress === 'incomplete') {
            $query->where(function ($q) {
                $q
                    ->whereNull('nisn')->orWhere('nisn', '')
                    ->orWhereNull('nipd')->orWhere('nipd', '')
                    ->orWhereNull('nama')->orWhere('nama', '')
                    ->orWhereNull('tempat_lahir')->orWhere('tempat_lahir', '')
                    ->orWhereNull('tanggal_lahir')
                    ->orWhereNull('jenis_kelamin')->orWhere('jenis_kelamin', '')
                    ->orWhereNull('agama')->orWhere('agama', '')
                    ->orWhereNull('no_hp')->orWhere('no_hp', '')
                    ->orWhereNull('alamat')->orWhere('alamat', '')
                    ->orWhereNull('rt')->orWhere('rt', '')
                    ->orWhereNull('rw')->orWhere('rw', '')
                    ->orWhereNull('dusun')->orWhere('dusun', '')
                    ->orWhereNull('kelurahan')->orWhere('kelurahan', '')
                    ->orWhereNull('kecamatan')->orWhere('kecamatan', '')
                    ->orWhereDoesntHave('media', function ($mq) {
                        $mq->where('collection_name', 'profile_photos');
                    })
                    ->orWhere(function ($emailQ) {
                        $emailQ
                            ->whereDoesntHave('user', function ($uq) {
                                $uq->whereNotNull('email')->where('email', '!=', '');
                            })
                            ->whereDoesntHave('account', function ($aq) {
                                $aq->whereNotNull('email')->where('email', '!=', '');
                            });
                    });
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if ($perPage <= 0) {
            $perPage = 10;
        }

        $sortField = $request->input('sort_by', 'nama');
        $sortOrder = $request->input('sort_order', 'asc');
        $allowedSortFields = ['nama', 'nisn', 'nipd', 'created_at', 'kelas'];
        if (! in_array($sortField, $allowedSortFields, true)) {
            $sortField = 'nama';
            $sortOrder = 'asc';
        }
        if (! in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'asc';
        }

        if ($sortField === 'kelas') {
            $query->orderBy('kelas.nama_kelas', $sortOrder)
                  ->orderBy('siswas.nama', 'asc');
        } else {
            $query->orderBy('siswas.'.$sortField, $sortOrder)
                  ->orderBy('kelas.nama_kelas', 'asc');
        }

        $siswas = $query->paginate($perPage)->withQueryString();

        $jurusanOptions = Cache::remember('options.jurusan', 86400, function () {
            return Jurusan::query()->select(['id', 'kode_jurusan', 'nama_jurusan'])
                ->orderBy('nama_jurusan')
                ->get();
        });

        $kelasOptions = Cache::remember('options.kelas_with_jurusan', 86400, function () {
            return Kelas::query()->select(['id', 'jurusan_id', 'nama_kelas'])
                ->orderByNamaKelas()
                ->get();
        });

        return Inertia::render('Admin/Siswa/Index', [
            'siswas' => $siswas,
            'filters' => $request->only(['search', 'kelas_id', 'jurusan_id', 'per_page', 'jenis_kelamin', 'progress', 'sort_by', 'sort_order']),
            'jurusanOptions' => $jurusanOptions,
            'kelasOptions' => $kelasOptions,
        ]);
    }

    public function create()
    {
        $jurusanOptions = Cache::remember('options.jurusan', 86400, function () {
            return Jurusan::query()->select(['id', 'kode_jurusan', 'nama_jurusan'])
                ->orderBy('nama_jurusan')
                ->get();
        });

        $kelasOptions = Cache::remember('options.kelas_with_jurusan', 86400, function () {
            return Kelas::query()->select(['id', 'jurusan_id', 'nama_kelas'])
                ->orderByNamaKelas()
                ->get();
        });

        return Inertia::render('Admin/Siswa/Create', [
            'jurusanOptions' => $jurusanOptions,
            'kelasOptions' => $kelasOptions,
        ]);
    }

    public function store(Request $request)
    {
        $messages = [
            'required' => ':attribute wajib diisi.',
            'unique' => ':attribute sudah digunakan.',
            'exists' => ':attribute tidak valid.',
            'in' => ':attribute tidak valid.',
            'date' => ':attribute harus berupa tanggal yang benar.',
        ];

        $attributes = [
            'kelas_id' => 'Kelas',
            'nisn' => 'NISN',
            'nipd' => 'NIPD',
            'nama' => 'Nama',
            'tempat_lahir' => 'Tempat lahir',
            'tanggal_lahir' => 'Tanggal lahir',
            'jenis_kelamin' => 'Jenis kelamin',
            'agama' => 'Agama',
            'rt' => 'RT',
            'rw' => 'RW',
            'dusun' => 'Dusun',
            'kelurahan' => 'Kelurahan',
            'kecamatan' => 'Kecamatan',
            'no_hp' => 'No HP',
            'alamat' => 'Alamat',
        ];

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'nisn' => 'required|string|unique:siswas,nisn',
            'nipd' => 'nullable|string|max:50',
            'nama' => 'required|string|max:255',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'nullable|string|max:50',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'dusun' => 'nullable|string|max:255',
            'kelurahan' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
        ], $messages, $attributes);

        DB::transaction(function () use ($validated) {
            // Create User for Siswa
            $email = $validated['nisn'].'@siswa.sekolah.com';
            $password = $validated['nisn']; // Default password is NISN

            // Check if user with email already exists (edge case)
            $existingUser = User::query()->where('email', $email)->first();
            if ($existingUser) {
                // If user exists, we might reuse it or fail.
                // For now, let's assume we reuse it if it doesn't have a siswa attached, or fail if it does.
                // Simplest approach: Use existing user.
                $user = $existingUser;
                if ($user->email_verified_at === null) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }
            } else {
                $user = User::create([
                    'name' => $validated['nama'],
                    'email' => $email,
                    'username' => $validated['nisn'], // Use NISN as username
                    'password' => $password,
                    'email_verified_at' => now(),
                ]);
                $user->assignRole('siswa');
            }

            $validated['user_id'] = $user->id;
            Siswa::create($validated);
        });

        Cache::forget('admin_dashboard_stats');

        return redirect()->route('admin.siswa.index')->with('message', 'Siswa berhasil ditambahkan (User dibuat dengan email: NISN@siswa.sekolah.com, password: NISN).');
    }

    public function edit(Siswa $siswa)
    {
        $jurusanOptions = Cache::remember('options.jurusan', 86400, function () {
            return Jurusan::query()->select(['id', 'kode_jurusan', 'nama_jurusan'])
                ->orderBy('nama_jurusan')
                ->get();
        });

        $kelasOptions = Cache::remember('options.kelas_with_jurusan', 86400, function () {
            return Kelas::query()->select(['id', 'jurusan_id', 'nama_kelas'])
                ->orderByNamaKelas()
                ->get();
        });

        return Inertia::render('Admin/Siswa/Edit', [
            'siswa' => $siswa->load('kelas.jurusan'),
            'jurusanOptions' => $jurusanOptions,
            'kelasOptions' => $kelasOptions,
        ]);
    }

    public function update(Request $request, Siswa $siswa)
    {
        $messages = [
            'required' => ':attribute wajib diisi.',
            'unique' => ':attribute sudah digunakan.',
            'exists' => ':attribute tidak valid.',
            'in' => ':attribute tidak valid.',
            'date' => ':attribute harus berupa tanggal yang benar.',
        ];

        $attributes = [
            'kelas_id' => 'Kelas',
            'nisn' => 'NISN',
            'nipd' => 'NIPD',
            'nama' => 'Nama',
            'tempat_lahir' => 'Tempat lahir',
            'tanggal_lahir' => 'Tanggal lahir',
            'jenis_kelamin' => 'Jenis kelamin',
            'agama' => 'Agama',
            'rt' => 'RT',
            'rw' => 'RW',
            'dusun' => 'Dusun',
            'kelurahan' => 'Kelurahan',
            'kecamatan' => 'Kecamatan',
            'no_hp' => 'No HP',
            'alamat' => 'Alamat',
        ];

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'nisn' => 'required|string|unique:siswas,nisn,'.$siswa->id,
            'nipd' => 'nullable|string|max:50',
            'nama' => 'required|string|max:255',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'nullable|string|max:50',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'dusun' => 'nullable|string|max:255',
            'kelurahan' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
        ], $messages, $attributes);

        DB::transaction(function () use ($siswa, $validated) {
            $oldNisn = $siswa->nisn;
            $siswa->update($validated);

            // Update User if exists
            if ($siswa->user) {
                $userData = ['name' => $validated['nama']];
                // Update email if NISN changed
                if ($oldNisn !== $validated['nisn']) {
                    $newEmail = $validated['nisn'].'@siswa.sekolah.com';
                    // Check uniqueness
                    if (! User::query()->where('email', $newEmail)->where('id', '!=', $siswa->user_id)->exists()) {
                        $userData['email'] = $newEmail;
                        $userData['username'] = $validated['nisn'];
                        if ($siswa->user->email_verified_at === null) {
                            $userData['email_verified_at'] = now();
                        }
                        // Optional: Reset password to new NISN? Maybe not, keep old password.
                    }
                }
                $siswa->user->update($userData);
            }
        });

        Cache::forget('admin_dashboard_stats');

        return redirect()->route('admin.siswa.index')->with('message', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        DB::transaction(function () use ($siswa) {
            $user = $siswa->user;
            $siswa->delete();
            if ($user) {
                $user->delete();
            }
        });

        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('message', 'Siswa berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:siswas,id',
        ]);

        DB::transaction(function () use ($validated) {
            Siswa::query()->whereIn('id', $validated['ids'])
                ->with('user')
                ->get()
                ->each(function (Siswa $siswa) {
                    $user = $siswa->user;
                    $siswa->delete();
                    if ($user) {
                        $user->delete();
                    }
                });
        });

        Cache::forget('admin_dashboard_stats');

        return redirect()->back()->with('message', 'Data siswa terpilih berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new \App\Exports\SiswaTemplateExport, 'template_siswa.xlsx');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['search', 'jurusan_id', 'kelas_id', 'jenis_kelamin']);

        return Excel::download(new \App\Exports\SiswaExport($filters), 'data_siswa.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $file = $request->file('file');
        $extension = strtolower((string) ($file?->getClientOriginalExtension() ?: $file?->extension()));
        if ($extension === 'xlsx' && ! class_exists(\ZipArchive::class)) {
            return redirect()->back()->with('error', 'Import gagal: server belum mengaktifkan ekstensi ZIP (ZipArchive). Aktifkan ext-zip atau gunakan file CSV.');
        }

        try {
            @ini_set('memory_limit', '512M');
            @set_time_limit(0);

            Excel::import(new \App\Imports\SiswaImport, $file);

            Cache::forget('admin_dashboard_stats');

            return redirect()->back()->with('message', 'Data siswa berhasil diimport.');
        } catch (\Throwable $e) {
            Log::error('Import siswa gagal', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            $message = $e->getMessage();
            if (stripos($message, 'ZipArchive') !== false || stripos($message, 'zip') !== false) {
                $message = 'Server belum mengaktifkan ekstensi ZIP (ZipArchive) untuk membaca file .xlsx. Aktifkan ext-zip atau gunakan file CSV.';
            } elseif (stripos($message, 'Maximum execution time') !== false) {
                $message = 'Proses import melebihi batas waktu (timeout). Kurangi jumlah baris per import atau naikkan max_execution_time di hosting.';
            } elseif (stripos($message, 'Allowed memory size') !== false) {
                $message = 'Proses import kehabisan memori. Kurangi jumlah baris per import atau naikkan memory_limit di hosting.';
            }

            return redirect()->back()->with('error', 'Import gagal: '.$message);
        }
    }
}
