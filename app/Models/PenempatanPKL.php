<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenempatanPKL extends Model
{
    protected $fillable = [
        'bengkel_id',
        'siswa_id',
        'pembimbing_id',
    ];

    public function bengkel(): BelongsTo
    {
        return $this->belongsTo(Bengkel::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pembimbing(): BelongsTo
    {
        return $this->belongsTo(Pembimbing::class);
    }
}
