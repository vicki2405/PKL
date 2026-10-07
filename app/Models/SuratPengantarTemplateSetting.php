<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratPengantarTemplateSetting extends Model
{
    protected $fillable = [
        'default_nomor_surat',
        'default_lampiran',
        'default_perihal',
        'default_tanggal_mulai',
        'default_tanggal_selesai',
        'kepala_sekolah_nama',
        'kepala_sekolah_nbm',
        'kepala_sekolah_ttd_path',
        'kepala_sekolah_stempel_path',
        'ketua_pkl_nama',
        'ketua_pkl_nbm',
        'ketua_pkl_ttd_path',
        'ketua_pkl_stempel_path',
        'kop_surat_path',
    ];
}
