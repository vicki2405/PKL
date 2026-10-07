<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class KarirSetting extends Model
{
    protected $table = 'karir_settings';

    protected $fillable = [
        'is_open',
        'pesan_penutupan',
        'opened_at',
        'closed_at',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Mendapatkan setting aktif atau default jika belum ada di database.
     */
    public static function getSetting(): self
    {
        return Cache::remember('karir.portal_setting', 3600, function () {
            if (Schema::hasTable('karir_settings')) {
                $setting = self::query()->first();
                if ($setting) {
                    if ($setting->updated_by) {
                        $setting->load('updatedBy:id,name');
                    }
                    return $setting;
                }

                // Default jika tabel ada tapi belum ada record
                try {
                    return self::create([
                        'is_open' => true,
                        'pesan_penutupan' => 'Pengisian rencana karier saat ini sedang ditutup oleh pihak sekolah.',
                        'opened_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    // Fallback jika create gagal
                }
            }

            // Fallback in-memory object jika tabel belum dimigrasi di server hosting
            $mock = new self([
                'is_open' => true,
                'pesan_penutupan' => 'Pengisian rencana karier saat ini sedang ditutup oleh pihak sekolah.',
                'opened_at' => now(),
            ]);
            $mock->id = 1;
            return $mock;
        });
    }

    /**
     * Cek apakah portal pengisian siswa sedang dibuka.
     */
    public static function isOpen(): bool
    {
        return (bool) self::getSetting()->is_open;
    }

    /**
     * Toggle status buka / tutup portal karir.
     */
    public static function toggleStatus(?int $userId = null, ?string $pesan = null): self
    {
        if (! Schema::hasTable('karir_settings')) {
            $mock = self::getSetting();
            $mock->is_open = ! $mock->is_open;
            return $mock;
        }

        $setting = self::query()->first();
        if (! $setting) {
            $setting = new self();
            $setting->is_open = true;
        }

        $newStatus = ! $setting->is_open;
        $setting->is_open = $newStatus;
        if ($newStatus) {
            $setting->opened_at = now();
            $setting->closed_at = null;
        } else {
            $setting->closed_at = now();
            if ($pesan !== null && trim($pesan) !== '') {
                $setting->pesan_penutupan = trim($pesan);
            }
        }
        $setting->updated_by = $userId;
        $setting->save();

        Cache::forget('karir.portal_setting');

        if ($setting->updated_by) {
            $setting->load('updatedBy:id,name');
        }

        return $setting;
    }
}
