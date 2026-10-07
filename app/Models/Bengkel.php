<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $kode_bengkel
 * @property string $nama_bengkel
 * @property string|null $nama_pemilik
 * @property string|null $alamat
 * @property string|null $dusun
 * @property string|null $desa
 * @property string|null $kecamatan
 * @property string|null $deskripsi
 * @property string|null $google_maps_link
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $no_hp
 */
class Bengkel extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_bengkel',
        'nama_bengkel',
        'nama_pemilik',
        'dusun',
        'desa',
        'kecamatan',
        'deskripsi',
        'google_maps_link',
        'latitude',
        'longitude',
        'no_hp',
    ];

    public function penempatan_pkl()
    {
        return $this->hasMany(PenempatanPKL::class);
    }

    public function monitorings()
    {
        return $this->hasMany(Monitoring::class);
    }
}
