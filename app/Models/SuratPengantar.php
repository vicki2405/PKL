<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SuratPengantar extends Model
{
    protected $fillable = [
        'nomor_surat',
        'lampiran',
        'tujuan_surat',
        'alamat_tujuan',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_surat',
        'kepala_sekolah_nama',
        'kepala_sekolah_nbm',
        'ketua_pkl_nama',
        'ketua_pkl_nbm',
    ];

    public function siswas(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'surat_pengantar_siswa');
    }
}
