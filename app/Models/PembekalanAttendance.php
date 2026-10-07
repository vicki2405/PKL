<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembekalanAttendance extends Model
{
    protected $fillable = [
        'siswa_id',
        'tanggal',
        'hadir',
        'izin',
        'sakit',
        'alpha',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
}
