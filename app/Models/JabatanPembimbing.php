<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JabatanPembimbing extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_jabatan',
        'deskripsi',
    ];
}
