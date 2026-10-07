<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembekalanAktivasi extends Model
{
    protected $fillable = [
        'tanggal',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
