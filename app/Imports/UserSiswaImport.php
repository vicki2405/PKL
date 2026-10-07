<?php

namespace App\Imports;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaUser;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserSiswaImport implements ToCollection, WithHeadingRow
{
    protected array $jurusanMap = [];

    protected array $kelasMap = [];

    public function __construct()
    {
        $jurusans = Jurusan::all();
        foreach ($jurusans as $j) {
            $key = $this->normalizeString($j->nama_jurusan);
            $this->jurusanMap[$key] = $j->id;
            if ($j->kode_jurusan) {
                $this->jurusanMap[$this->normalizeString($j->kode_jurusan)] = $j->id;
            }
        }

        $kelases = Kelas::all();
        foreach ($kelases as $k) {
            $key = $k->jurusan_id.'|'.$this->normalizeString($k->nama_kelas);
            $this->kelasMap[$key] = $k->id;
        }
    }

    /**
     * @param string|null $str
     */
    protected function normalizeString($str): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) ($str ?? '')));
    }

    /**
     * @param string|null $jurusanNama
     * @param string|null $kelasNama
     */
    protected function resolveKelasId($jurusanNama, $kelasNama): ?int
    {
        if (empty($kelasNama)) {
            return null;
        }

        $normKelas = $this->normalizeString($kelasNama);
        $jurusanId = null;

        if (! empty($jurusanNama)) {
            $normJurusan = $this->normalizeString($jurusanNama);

            if (isset($this->jurusanMap[$normJurusan])) {
                $jurusanId = $this->jurusanMap[$normJurusan];
            } else {
                foreach ($this->jurusanMap as $key => $id) {
                    if ($key !== '' && (str_contains($key, $normJurusan) || str_contains($normJurusan, $key))) {
                        $jurusanId = $id;
                        break;
                    }
                }
            }

            if (! $jurusanId && $jurusanNama !== '') {
                $jObj = Jurusan::query()->firstOrCreate(
                    ['nama_jurusan' => trim($jurusanNama)],
                    ['kode_jurusan' => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $jurusanNama), 0, 5))]
                );
                $jurusanId = $jObj->id;
                $this->jurusanMap[$normJurusan] = $jurusanId;
            }
        }

        if (! $jurusanId) {
            $jObj = Jurusan::query()->first();
            if (! $jObj) {
                $jObj = Jurusan::query()->create(['nama_jurusan' => 'Umum', 'kode_jurusan' => 'UMUM']);
            }
            $jurusanId = $jObj->id;
        }

        $kelasKey = $jurusanId.'|'.$normKelas;
        if (isset($this->kelasMap[$kelasKey])) {
            return $this->kelasMap[$kelasKey];
        }

        foreach ($this->kelasMap as $key => $id) {
            if (str_starts_with($key, $jurusanId.'|')) {
                $keyName = substr($key, strlen($jurusanId.'|'));
                if ($keyName === $normKelas || str_contains($keyName, $normKelas) || str_contains($normKelas, $keyName)) {
                    return $id;
                }
            }
        }

        // Auto Create Kelas if missing
        $kObj = Kelas::query()->firstOrCreate([
            'jurusan_id' => $jurusanId,
            'nama_kelas' => trim($kelasNama),
        ]);
        $this->kelasMap[$kelasKey] = $kObj->id;

        return $kObj->id;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                $nama = $row['nama'] ?? ($row['nama_siswa'] ?? null);
                $nisn = $row['nisn'] ?? null;
                $jurusanNama = $row['jurusan'] ?? null;
                $kelasNama = $row['kelas'] ?? null;
                $nipd = $row['nipd'] ?? null;
                $passwordPlain = $row['password'] ?? null;

                if (empty($nama) || empty($nisn)) {
                    continue;
                }

                $nisn = trim((string) $nisn);
                $nama = trim((string) $nama);

                $existingUser = SiswaUser::query()->where('username', '=', $nisn)->first();

                if ($existingUser) {
                    continue;
                }

                DB::transaction(function () use ($nama, $nisn, $jurusanNama, $kelasNama, $nipd, $passwordPlain) {
                    $email = $nisn.'@siswa.sekolah.com';
                    if (SiswaUser::query()->where('email', '=', $email)->exists() || User::query()->where('email', '=', $email)->exists()) {
                        $email = null;
                    }

                    $rawPassword = ! empty($passwordPlain) ? $passwordPlain : 'password';

                    $user = SiswaUser::query()->create([
                        'name' => $nama,
                        'username' => $nisn,
                        'email' => $email,
                        'password' => $rawPassword,
                        'plain_password' => $rawPassword,
                        'email_verified_at' => now(),
                    ]);

                    // Sync system User so direct login via Auth::attempt is instant
                    $systemUser = User::query()->where('username', '=', $nisn)->first();
                    if (! $systemUser && $email) {
                        $systemUser = User::query()->where('email', '=', $email)->first();
                    }

                    if (! $systemUser) {
                        $systemUser = User::query()->create([
                            'name' => $nama,
                            'username' => $nisn,
                            'email' => $email,
                            'password' => $rawPassword,
                            'email_verified_at' => now(),
                        ]);
                        $systemUser->assignRole('siswa');
                    }

                    $siswa = Siswa::query()->where('nisn', '=', $nisn)->first();
                    $kelasId = $this->resolveKelasId($jurusanNama, $kelasNama);

                    if (! $siswa) {
                        Siswa::query()->create([
                            'user_id' => $systemUser->id,
                            'siswa_user_id' => $user->id,
                            'user_account_enabled' => true,
                            'nama' => $nama,
                            'nisn' => $nisn,
                            'nipd' => $nipd,
                            'kelas_id' => $kelasId,
                        ]);
                    } else {
                        $siswa->update([
                            'user_id' => $systemUser->id,
                            'siswa_user_id' => $user->id,
                            'kelas_id' => $kelasId ?: $siswa->kelas_id,
                        ]);
                    }
                });

            } catch (\Exception $e) {
                Log::warning('Import user siswa dilewati: '.$e->getMessage(), ['row' => $row]);
            }
        }
    }
}
