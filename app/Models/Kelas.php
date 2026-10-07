<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'jurusan_id',
        'nama_kelas',
        'tingkat',
        'keterangan',
        'wali_kelas',
        'no_wa_wali',
    ];

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function scopeOrderByNamaKelas(Builder $query, string $direction = 'asc')
    {
        $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        if ($query->getConnection()->getDriverName() === 'sqlite') {
            return $query->orderBy('nama_kelas', $dir);
        }

        return $query->orderByRaw("
            TRIM(REGEXP_REPLACE(nama_kelas, '[0-9]+$', '')) {$dir},
            CAST(NULLIF(REGEXP_REPLACE(nama_kelas, '[^0-9]', ''), '') AS UNSIGNED) {$dir},
            nama_kelas {$dir}
        ");
    }
}
