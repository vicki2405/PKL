<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiPKL extends Model
{
    protected $table = 'mutasi_p_k_l_s';

    protected $fillable = [
        'siswa_id',
        'bengkel_asal_id',
        'bengkel_tujuan_id',
        'nama_bengkel_baru',
        'nama_pemilik_bengkel_baru',
        'dusun',
        'desa',
        'kecamatan',
        'deskripsi',
        'google_maps_link_baru',
        'pembimbing_id',
        'pembimbing_tujuan_id',
        'alasan',
        'status',
        'admin_note',
        'tanggal_surat',
        'admin_id',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function bengkelAsal(): BelongsTo
    {
        return $this->belongsTo(Bengkel::class, 'bengkel_asal_id');
    }

    public function bengkelTujuan(): BelongsTo
    {
        return $this->belongsTo(Bengkel::class, 'bengkel_tujuan_id');
    }

    /**
     * Pembimbing Asal (Lama) yang menandatangani Surat P-1
     */
    public function pembimbing(): BelongsTo
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }

    public function pembimbingAsal(): BelongsTo
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }

    /**
     * Pembimbing Baru di Bengkel Tujuan
     */
    public function pembimbingTujuan(): BelongsTo
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_tujuan_id');
    }
}
