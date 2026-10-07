<?php

namespace App\Imports;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SiswaImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    protected $kelasMap = [];

    protected $jurusanMap = [];

    public function __construct()
    {
        // Preload all Jurusan and Kelas to minimize DB queries and allow fuzzy matching
        $jurusans = Jurusan::all();
        foreach ($jurusans as $j) {
            $key = $this->normalizeString($j->nama_jurusan);
            $this->jurusanMap[$key] = $j->id;
            // Also map kode_jurusan just in case
            if ($j->kode_jurusan) {
                $this->jurusanMap[$this->normalizeString($j->kode_jurusan)] = $j->id;
            }
        }

        $kelases = Kelas::all();
        foreach ($kelases as $k) {
            // Map: jurusan_id|normalized_nama_kelas => kelas_id
            $key = $k->jurusan_id.'|'.$this->normalizeString($k->nama_kelas);
            $this->kelasMap[$key] = $k->id;
        }
    }

    protected function normalizeString(?string $str): string
    {
        // Remove non-alphanumeric, lowercase
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $str));
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function model(array $row)
    {
        $kelasId = $row['kelas_id'] ?? null;

        // Helper to clean strings
        $cleanString = fn ($val) => trim((string) ($val ?? ''));

        if (! $kelasId) {
            $kelasNama = $cleanString($row['kelas'] ?? '');
            $jurusanNama = $cleanString($row['jurusan'] ?? '');

            if ($kelasNama === '' || $jurusanNama === '') {
                return null;
            }

            // Fuzzy Match Jurusan
            $jurusanId = null;
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
                $jObj = Jurusan::firstOrCreate(
                    ['nama_jurusan' => $jurusanNama],
                    ['kode_jurusan' => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $jurusanNama), 0, 5))]
                );
                $jurusanId = $jObj->id;
                $this->jurusanMap[$normJurusan] = $jurusanId;
            }

            if (! $jurusanId) {
                return null;
            }

            // Fuzzy Match Kelas
            $normKelas = $this->normalizeString($kelasNama);
            $kelasKey = $jurusanId.'|'.$normKelas;

            if (isset($this->kelasMap[$kelasKey])) {
                $kelasId = $this->kelasMap[$kelasKey];
            } else {
                foreach ($this->kelasMap as $key => $id) {
                    if (str_starts_with($key, $jurusanId.'|')) {
                        $keyName = substr($key, strlen($jurusanId.'|'));
                        if ($keyName === $normKelas || str_contains($keyName, $normKelas) || str_contains($normKelas, $keyName)) {
                            $kelasId = $id;
                            break;
                        }
                    }
                }
            }

            if (! $kelasId && $kelasNama !== '') {
                $kObj = Kelas::firstOrCreate([
                    'jurusan_id' => $jurusanId,
                    'nama_kelas' => $kelasNama,
                ]);
                $kelasId = $kObj->id;
                $this->kelasMap[$kelasKey] = $kelasId;
            }

            if (! $kelasId) {
                return null;
            }
        }

        $nisn = $cleanString($row['nisn'] ?? '');
        if (strpos($nisn, 'E') !== false) {
            $nisn = number_format((float) $nisn, 0, '.', '');
        }

        $namaLengkap = $cleanString($row['nama_lengkap'] ?? ($row['nama'] ?? ''));

        $jkRaw = $cleanString($row['jenis_kelamin'] ?? ($row['jk'] ?? ''));
        $jk = strtoupper($jkRaw);

        if ($jk === 'LAKI-LAKI' || $jk === 'LAKI LAKI') {
            $jk = 'L';
        } elseif ($jk === 'PEREMPUAN') {
            $jk = 'P';
        }

        if ($kelasId === null || $nisn === '' || $namaLengkap === '' || ! in_array($jk, ['L', 'P'], true)) {
            return null;
        }

        // --- UPDATE LOGIC START ---
        // Check if Siswa exists by NISN
        $existingSiswa = Siswa::query()->where('nisn', $nisn)->first();

        // Prepare Date
        $tanggalLahir = $row['tanggal_lahir'] ?? null;
        $tanggalLahirFinal = null;

        if ($tanggalLahir) {
            if (is_numeric($tanggalLahir)) {
                try {
                    $tanggalLahirFinal = Date::excelToDateTimeObject($tanggalLahir)->format('Y-m-d');
                } catch (\Throwable $e) {
                    $tanggalLahirFinal = null;
                }
            } else {
                $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'];
                foreach ($formats as $format) {
                    try {
                        $date = \DateTime::createFromFormat($format, $tanggalLahir);
                        if ($date && $date->format($format) == $tanggalLahir) {
                            $tanggalLahirFinal = $date->format('Y-m-d');
                            break;
                        }
                    } catch (\Throwable $e) {
                        continue;
                    }
                }
                if (! $tanggalLahirFinal && strtotime($tanggalLahir)) {
                    $tanggalLahirFinal = date('Y-m-d', strtotime($tanggalLahir));
                }
            }
        }

        // Data to update/create
        $siswaData = [
            'kelas_id' => $kelasId,
            'nipd' => $row['nipd'] ?? null,
            'nama' => $namaLengkap,
            'tempat_lahir' => $row['tempat_lahir'] ?? null,
            'tanggal_lahir' => $tanggalLahirFinal,
            'jenis_kelamin' => $jk,
            'agama' => $row['agama'] ?? null,
            'no_hp' => $row['no_hp_wa'] ?? ($row['no_hp'] ?? null),
            'alamat' => $row['alamat'] ?? null,
            'rt' => $row['rt'] ?? null,
            'rw' => $row['rw'] ?? null,
            'dusun' => $row['dusun'] ?? null,
            'kelurahan' => $row['kelurahan'] ?? null,
            'kecamatan' => $row['kecamatan'] ?? null,
            'keterangan' => $row['keterangan'] ?? null,
        ];

        // Ensure User Exists
        $email = $cleanString($row['email'] ?? '');
        if ($email === '') {
            $email = $nisn.'@siswa.sekolah.com';
        }

        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            $user = User::create([
                'name' => $namaLengkap,
                'email' => $email,
                'username' => $nisn,
                'password' => $nisn,
            ]);
            $user->assignRole('siswa');
        } else {
            // Optional: Update user name?
            // $user->update(['name' => $namaLengkap]);
        }

        $siswaData['user_id'] = $user->id;

        if ($existingSiswa) {
            // Update existing record
            $existingSiswa->update($siswaData);

            return null; // Return null to skip insert, as we updated manually
        }

        // Return new model for insertion
        return new Siswa(array_merge($siswaData, ['nisn' => $nisn]));
    }
}
