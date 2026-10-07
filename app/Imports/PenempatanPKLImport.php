<?php

namespace App\Imports;

use App\Models\Bengkel;
use App\Models\Pembimbing;
use App\Models\PenempatanPKL;
use App\Models\Siswa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PenempatanPKLImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public int $inserted = 0;

    public int $updated = 0;

    public int $skipped = 0;

    public array $errors = [];

    protected bool $initialized = false;

    protected array $bengkelByKode = [];

    protected array $bengkelByKodeNorm = [];

    protected array $bengkelByName = [];

    protected array $pembimbingByName = [];

    protected array $siswaByName = [];

    protected array $siswaByNisn = [];

    /**
     * @param string|int|null $v
     */
    protected function norm($v): string
    {
        $s = trim((string) ($v ?? ''));

        return strtolower(preg_replace('/[^a-z0-9]/', '', $s));
    }

    /**
     * @param array $row
     * @param array $keys
     */
    protected function firstNonEmpty($row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (! isset($row[$key])) {
                continue;
            }
            $value = trim((string) ($row[$key] ?? ''));
            if ($value !== '' && $value !== '-' && $value !== '–') {
                return $value;
            }
        }

        return null;
    }

    protected function registerId(array &$map, string $key, int $id): void
    {
        if ($key === '') {
            return;
        }

        if (! isset($map[$key])) {
            $map[$key] = $id;

            return;
        }

        $existing = $map[$key];
        if (is_int($existing)) {
            if ($existing === $id) {
                return;
            }
            $map[$key] = [$existing, $id];

            return;
        }

        if (is_array($existing)) {
            if (in_array($id, $existing, true)) {
                return;
            }
            $existing[] = $id;
            $map[$key] = $existing;
        }
    }

    public function chunkSize(): int
    {
        return 200;
    }

    protected function initializeLookups(): void
    {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        $bengkels = Bengkel::query()->select(['id', 'kode_bengkel', 'nama_bengkel'])->get();
        foreach ($bengkels as $b) {
            $kode = trim((string) ($b->kode_bengkel ?? ''));
            if ($kode !== '') {
                $this->bengkelByKode[$kode] = $b->id;
                $kodeNorm = $this->norm($kode);
                $this->registerId($this->bengkelByKodeNorm, $kodeNorm, (int) $b->id);
            }
            $nameKey = $this->norm($b->nama_bengkel);
            if ($nameKey !== '' && ! isset($this->bengkelByName[$nameKey])) {
                $this->bengkelByName[$nameKey] = $b->id;
            }
        }

        $pembimbings = Pembimbing::query()->select(['id', 'nama_guru'])->get();
        foreach ($pembimbings as $p) {
            $nameKey = $this->norm($p->nama_guru);
            if ($nameKey !== '' && ! isset($this->pembimbingByName[$nameKey])) {
                $this->pembimbingByName[$nameKey] = $p->id;
            }
        }

        $siswas = Siswa::query()->select(['id', 'nama', 'nisn'])->get();
        foreach ($siswas as $s) {
            $nameKey = $this->norm($s->nama);
            $this->registerId($this->siswaByName, $nameKey, (int) $s->id);

            $nisn = trim((string) ($s->nisn ?? ''));
            if ($nisn !== '' && ! isset($this->siswaByNisn[$nisn])) {
                $this->siswaByNisn[$nisn] = $s->id;
            }
        }
    }

    public function collection(Collection $rows)
    {
        $this->initializeLookups();

        $now = now();
        $recordsBySiswaId = [];
        $siswaIdsInChunk = [];

        foreach ($rows as $i => $row) {
            $kodeBengkel = $row['kode_bengkel'] ?? null;
            $namaBengkel = $row['nama_bengkel'] ?? ($row['bengkel'] ?? null);
            $pembimbingNama = $row['pembimbing'] ?? null;

            // Cari Bengkel
            $bengkelId = null;
            $kodeKey = trim((string) ($kodeBengkel ?? ''));
            if ($kodeKey !== '' && isset($this->bengkelByKode[$kodeKey])) {
                $bengkelId = $this->bengkelByKode[$kodeKey];
            }
            if (! $bengkelId && $kodeKey !== '') {
                $kodeNorm = $this->norm($kodeKey);
                $match = $kodeNorm !== '' ? ($this->bengkelByKodeNorm[$kodeNorm] ?? null) : null;
                if (is_int($match)) {
                    $bengkelId = $match;
                } elseif (is_array($match)) {
                    $unique = array_values(array_unique(array_map('intval', $match)));
                    if (count($unique) === 1) {
                        $bengkelId = $unique[0];
                    } elseif (count($unique) > 1) {
                        $this->skipped++;
                        $this->errors[] = 'Baris '.($i + 2).': Kode Bengkel "'.$kodeKey.'" ambigu. Gunakan Kode Bengkel persis seperti di data Bengkel';

                        continue;
                    }
                }
            }
            if (! $bengkelId && $namaBengkel) {
                $normNama = $this->norm($namaBengkel);
                if ($normNama !== '' && isset($this->bengkelByName[$normNama])) {
                    $bengkelId = $this->bengkelByName[$normNama];
                } elseif ($normNama !== '') {
                    $candidateIds = [];
                    foreach ($this->bengkelByName as $key => $id) {
                        if (str_contains($key, $normNama) || str_contains($normNama, $key)) {
                            $candidateIds[(int) $id] = true;
                        }
                    }
                    $candidateIds = array_keys($candidateIds);
                    if (count($candidateIds) === 1) {
                        $bengkelId = $candidateIds[0];
                    } elseif (count($candidateIds) > 1) {
                        $this->skipped++;
                        $this->errors[] = 'Baris '.($i + 2).': Bengkel "'.$namaBengkel.'" ambigu. Gunakan Kode Bengkel agar pasti cocok';
                        continue;
                    }
                }
            }

            if (! $bengkelId && ($namaBengkel || $kodeBengkel)) {
                $bName = trim((string) ($namaBengkel ?: $kodeBengkel));
                if ($bName !== '') {
                    $bObj = Bengkel::query()->create([
                        'nama_bengkel' => $bName,
                        'kode_bengkel' => $kodeBengkel ? trim((string) $kodeBengkel) : null,
                    ]);
                    $bengkelId = $bObj->id;
                    $this->bengkelByName[$this->norm($bName)] = $bengkelId;
                }
            }

            if (! $bengkelId) {
                $this->skipped++;
                $this->errors[] = 'Baris '.($i + 2).': Bengkel "'.($namaBengkel ?? $kodeBengkel ?? 'Kosong').'" tidak ditemukan';

                continue;
            }

            // Cari Pembimbing (Opsional)
            $pembimbingId = null;
            if ($pembimbingNama) {
                $normPemb = $this->norm($pembimbingNama);
                if ($normPemb !== '' && isset($this->pembimbingByName[$normPemb])) {
                    $pembimbingId = $this->pembimbingByName[$normPemb];
                } else {
                    $pObj = Pembimbing::query()->create([
                        'nama_guru' => trim((string) $pembimbingNama),
                    ]);
                    $pembimbingId = $pObj->id;
                    $this->pembimbingByName[$normPemb] = $pembimbingId;
                }
            }

            $hasAnySiswaCell = false;
            for ($j = 1; $j <= 10; $j++) {
                $namaSiswa = $this->firstNonEmpty($row, [
                    'nama_'.$j,
                    'nama'.$j,
                    'nama_siswa_'.$j,
                    'nama_siswa'.$j,
                ]);

                if (! $namaSiswa) {
                    continue;
                }
                $hasAnySiswaCell = true;

                $normSiswa = $this->norm($namaSiswa);

                $nisnKey = trim((string) ($row['nisn_'.$j] ?? $row['nisn'.$j] ?? ''));
                $siswaId = $nisnKey !== '' ? ($this->siswaByNisn[$nisnKey] ?? null) : null;

                if (! $siswaId) {
                    if (empty($normSiswa)) {
                        continue;
                    }
                    $match = $this->siswaByName[$normSiswa] ?? null;
                    if (is_int($match)) {
                        $siswaId = $match;
                    } elseif (is_array($match)) {
                        $unique = array_values(array_unique(array_map('intval', $match)));
                        if (count($unique) === 1) {
                            $siswaId = $unique[0];
                        } elseif (count($unique) > 1) {
                            $this->skipped++;
                            $this->errors[] = 'Baris '.($i + 2).', Kolom Siswa '.$j.': Nama siswa "'.$namaSiswa.'" ambigu (lebih dari 1 siswa). Tambahkan kolom NISN '.$j.' agar pasti cocok';

                            continue;
                        }
                    }
                }

                if (! $siswaId) {
                    $candidateIds = [];
                    foreach ($this->siswaByName as $key => $idOrIds) {
                        if (! str_contains($key, $normSiswa) && ! str_contains($normSiswa, $key)) {
                            continue;
                        }
                        if (is_int($idOrIds)) {
                            $candidateIds[(int) $idOrIds] = true;
                        } elseif (is_array($idOrIds)) {
                            foreach ($idOrIds as $cid) {
                                $candidateIds[(int) $cid] = true;
                            }
                        }
                    }
                    $candidateIds = array_keys($candidateIds);
                    if (count($candidateIds) === 1) {
                        $siswaId = $candidateIds[0];
                    } elseif (count($candidateIds) > 1) {
                        $this->skipped++;
                        $this->errors[] = 'Baris '.($i + 2).', Kolom Siswa '.$j.': Siswa "'.$namaSiswa.'" ambigu. Tambahkan kolom NISN '.$j.' agar pasti cocok';

                        continue;
                    }
                }

                if (! $siswaId) {
                    $this->skipped++;
                    $this->errors[] = 'Baris '.($i + 2).', Kolom Siswa '.$j.': Siswa "'.$namaSiswa.'" tidak ditemukan';

                    continue;
                }

                // Create/Update Penempatan
                $recordsBySiswaId[$siswaId] = [
                    'siswa_id' => $siswaId,
                    'bengkel_id' => $bengkelId,
                    'pembimbing_id' => $pembimbingId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $siswaIdsInChunk[$siswaId] = true;
            }

            if (! $hasAnySiswaCell) {
                $this->skipped++;
                $this->errors[] = 'Baris '.($i + 2).': Tidak ada data siswa (Nama 1..10 kosong atau header tidak sesuai template)';
            }
        }

        $siswaIds = array_keys($siswaIdsInChunk);
        if (count($siswaIds) === 0) {
            return;
        }

        $existingIds = DB::table('penempatan_pkl')
            ->whereIn('siswa_id', $siswaIds)
            ->pluck('siswa_id')
            ->all();

        $existingMap = array_fill_keys(array_map('intval', $existingIds), true);
        $updated = 0;
        $inserted = 0;
        foreach ($siswaIds as $sid) {
            if (isset($existingMap[(int) $sid])) {
                $updated++;
            } else {
                $inserted++;
            }
        }

        DB::transaction(function () use ($recordsBySiswaId) {
            DB::table('penempatan_pkl')->upsert(
                array_values($recordsBySiswaId),
                ['siswa_id'],
                ['bengkel_id', 'pembimbing_id', 'updated_at']
            );
        });

        $this->updated += $updated;
        $this->inserted += $inserted;
    }

    public function summary(): array
    {
        return [
            'inserted' => $this->inserted,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
        ];
    }
}
