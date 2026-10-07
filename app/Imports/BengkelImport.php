<?php

namespace App\Imports;

use App\Models\Bengkel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BengkelImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public int $inserted = 0;

    public int $updated = 0;

    public int $skipped = 0;

    public array $errors = [];

    protected bool $initialized = false;

    protected array $bengkelByKode = [];

    protected array $bengkelByName = [];

    public function chunkSize(): int
    {
        return 200;
    }

    protected function norm($v): string
    {
        $s = trim((string) ($v ?? ''));

        return strtolower(preg_replace('/[^a-z0-9]/', '', $s));
    }

    protected function normalizeOptionalString($value): ?string
    {
        $v = trim((string) ($value ?? ''));
        if ($v === '') {
            return null;
        }

        if ($v === '-' || $v === '–' || strtolower($v) === 'null') {
            return null;
        }

        return $v;
    }

    protected function normalizeNumber($value): ?string
    {
        $v = trim((string) ($value ?? ''));
        if ($v === '') {
            return null;
        }

        if ($v === '-' || $v === '–' || strtolower($v) === 'null') {
            return null;
        }

        $v = str_replace(',', '.', $v);

        return is_numeric($v) ? $v : null;
    }

    protected function initializeLookups(): void
    {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        $bengkels = Bengkel::select('id', 'kode_bengkel', 'nama_bengkel')->get();
        foreach ($bengkels as $b) {
            $kode = trim((string) ($b->kode_bengkel ?? ''));
            if ($kode !== '' && ! isset($this->bengkelByKode[$kode])) {
                $this->bengkelByKode[$kode] = $b->id;
            }
            $nameKey = $this->norm($b->nama_bengkel);
            if ($nameKey !== '' && ! isset($this->bengkelByName[$nameKey])) {
                $this->bengkelByName[$nameKey] = $b->id;
            }
        }
    }

    public function collection(Collection $rows)
    {
        $this->initializeLookups();

        DB::transaction(function () use ($rows) {
            foreach ($rows as $i => $row) {
                $nama = trim((string) ($row['nama_bengkel'] ?? ''));
                if ($nama === '') {
                    $this->skipped++;
                    $this->errors[] = 'Baris '.($i + 2).': Nama Bengkel kosong';
                    continue;
                }

                $kode = trim((string) ($row['kode_bengkel'] ?? ''));
                $nameKey = $this->norm($nama);

                $existingId = null;
                if ($kode !== '' && isset($this->bengkelByKode[$kode])) {
                    $existingId = $this->bengkelByKode[$kode];
                } elseif ($nameKey !== '' && isset($this->bengkelByName[$nameKey])) {
                    $existingId = $this->bengkelByName[$nameKey];
                }

                $data = [
                    'kode_bengkel' => $this->normalizeOptionalString($kode),
                    'nama_bengkel' => $nama,
                    'nama_pemilik' => $this->normalizeOptionalString($row['nama_pemilik'] ?? null),
                    'dusun' => $this->normalizeOptionalString($row['dusun'] ?? null),
                    'desa' => $this->normalizeOptionalString($row['desa'] ?? null),
                    'kecamatan' => $this->normalizeOptionalString($row['kecamatan'] ?? null),
                    'deskripsi' => $this->normalizeOptionalString($row['deskripsi'] ?? null),
                    'latitude' => $this->normalizeNumber($row['latitude'] ?? null),
                    'longitude' => $this->normalizeNumber($row['longitude'] ?? null),
                    'google_maps_link' => $this->normalizeOptionalString($row['google_maps_link'] ?? null),
                    'no_hp' => $this->normalizeOptionalString($row['no_hp'] ?? null),
                ];

                if ($existingId) {
                    Bengkel::whereKey($existingId)->update($data);
                    $this->updated++;
                } else {
                    $created = Bengkel::create($data);
                    $this->inserted++;

                    if ($kode !== '') {
                        $this->bengkelByKode[$kode] = $created->id;
                    }
                    if ($nameKey !== '') {
                        $this->bengkelByName[$nameKey] = $created->id;
                    }
                }
            }
        });
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
