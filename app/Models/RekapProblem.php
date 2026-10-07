<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapProblem extends Model
{
    protected $fillable = [
        'tanggal',
        'siswa_id',
        'bengkel_id',
        'pembimbing_id',
        'deskripsi_masalah',
        'deskripsi_tindakan',
        'saksi',
        'admin_id',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function bengkel()
    {
        return $this->belongsTo(Bengkel::class);
    }

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class);
    }
}
