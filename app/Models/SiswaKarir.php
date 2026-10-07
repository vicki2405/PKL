<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiswaKarir extends Model
{
    protected $fillable = [
        'siswa_id',
        'tipe_karir',
        'edit_count',

        // Kuliah Columns
        'nama_universitas',
        'status_universitas',
        'jenjang_studi',
        'program_studi',
        'jalur_masuk',
        'tahun_mulai',

        // Kerja Columns
        'nama_perusahaan',
        'posisi_kerja',
        'jenis_pekerjaan',
        'kesesuaian_jurusan',
        'target_mulai_kerja',
        'ekspektasi_gaji',
        'lokasi_kerja',

        // Wirausaha Columns
        'nama_usaha',
        'sektor_usaha',
        'status_kepemilikan',
        'modal_awal',
        'lokasi_usaha',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
}
