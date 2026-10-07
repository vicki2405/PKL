<?php

namespace App\Imports;

use App\Models\JabatanPembimbing;
use App\Models\Pembimbing;
use App\Models\PembimbingUser;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserPembimbingImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private array $existingPembimbingUsers = [];
    private array $existingUsers = [];
    private array $existingJabatans = [];
    private array $existingPembimbings = [];
    private int $pembimbingUserSuffix = 1;
    private int $userSuffix = 1;

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $this->processRow($row);
            }
        });
    }

    private function processRow(array $row): void
    {
        $name = trim((string) ($row['nama'] ?? ($row['nama_guru'] ?? ($row['nama_pembimbing'] ?? ''))));
        if (empty($name)) {
            return;
        }

        $email = trim((string) ($row['email'] ?? ''));
        if (empty($email)) {
            $email = Str::slug($name) . '@pembimbing.com';
        }

        $password = trim((string) ($row['password'] ?? 'password'));
        $pembimbingId = trim((string) ($row['pembimbing_id'] ?? ''));
        $jabatanName = trim((string) ($row['jabatan'] ?? ''));
        $jenisKelamin = trim((string) ($row['jenis_kelamin'] ?? ($row['jk'] ?? 'L')));
        $alamat = trim((string) ($row['alamat'] ?? ''));
        $noHp = trim((string) ($row['no_hp'] ?? ''));

        // Skip if PembimbingUser email already exists
        if (PembimbingUser::query()->where('email', '=', $email)->exists()) {
            return;
        }

        $baseUsername = Str::slug($name);
        $username = $this->getUniqueUsername($baseUsername, 'pembimbing_users');
        $sysUsername = $this->getUniqueUsername($baseUsername, 'users');

        $jabatanId = null;
        if ($jabatanName !== '') {
            $jabatanId = $this->getOrCreateJabatan($jabatanName);
        }

        $pembimbing = $this->findOrCreatePembimbing($pembimbingId, $name, $jabatanId, $jenisKelamin, $alamat, $noHp);

        // Create PembimbingUser
        $pembimbingUser = PembimbingUser::query()->create([
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'password' => $password,
            'email_verified_at' => now(),
        ]);

        // Create User (system) if email/username unique
        $systemUser = User::query()->where('email', '=', $email)->first();
        if (!$systemUser) {
            $systemUser = User::query()->create([
                'name' => $name,
                'email' => $email,
                'username' => $sysUsername,
                'password' => $password,
            ]);
            $systemUser->assignRole('pembimbing');
        }

        // Link everything
        $pembimbing->update([
            'pembimbing_user_id' => $pembimbingUser->id,
            'user_id' => $systemUser->id,
        ]);

        Log::info("User pembimbing berhasil diimport: {$name} ({$email})");
    }

    private function findOrCreatePembimbing(string $pembimbingId, string $name, ?int $jabatanId, string $jenisKelamin, string $alamat, string $noHp): Pembimbing
    {
        $cacheKey = $pembimbingId ?: $name;
        if (isset($this->existingPembimbings[$cacheKey])) {
            return $this->existingPembimbings[$cacheKey];
        }

        $pembimbing = null;

        if ($pembimbingId !== '') {
            $pembimbing = Pembimbing::query()->find($pembimbingId);
        }

        if (!$pembimbing) {
            $pembimbing = Pembimbing::query()->where('nama_guru', '=', $name)->first();
        }

        if (!$pembimbing) {
            $pembimbing = new Pembimbing();
        }

        $pembimbing->nama_guru = $name;
        if ($jabatanId) {
            $pembimbing->jabatan_pembimbing_id = $jabatanId;
        }
        $pembimbing->jenis_kelamin = $jenisKelamin ?: 'L';
        $pembimbing->alamat = $alamat;
        $pembimbing->no_hp = $noHp;
        $pembimbing->save();

        $this->existingPembimbings[$cacheKey] = $pembimbing;

        return $pembimbing;
    }

    private function getUniqueUsername(string $base, string $table): string
    {
        if ($table === 'pembimbing_users') {
            $cache = &$this->existingPembimbingUsers;
            $suffix = &$this->pembimbingUserSuffix;
        } else {
            $cache = &$this->existingUsers;
            $suffix = &$this->userSuffix;
        }

        $username = $base;
        while (isset($cache[$username]) || $this->usernameExistsInDb($username, $table)) {
            $username = $base . '-' . $suffix++;
        }
        $cache[$username] = true;
        return $username;
    }

    private function usernameExistsInDb(string $username, string $table): bool
    {
        return $table === 'pembimbing_users'
            ? PembimbingUser::query()->where('username', '=', $username)->exists()
            : User::query()->where('username', '=', $username)->exists();
    }

    private function getOrCreateJabatan(string $name): int
    {
        if (isset($this->existingJabatans[$name])) {
            return $this->existingJabatans[$name];
        }
        $jabatan = JabatanPembimbing::query()->firstOrCreate(['nama_jabatan' => $name]);
        $this->existingJabatans[$name] = $jabatan->id;
        return $jabatan->id;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
