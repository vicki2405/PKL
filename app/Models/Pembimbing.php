<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $pembimbing_user_id
 * @property string $nama_guru
 * @property string|null $nip
 * @property string|null $jenis_kelamin
 * @property string|null $alamat
 * @property string|null $no_hp
 * @property int $jabatan_pembimbing_id
 * @property \App\Models\User|null $user
 * @property \App\Models\PembimbingUser|null $account
 * @property \App\Models\JabatanPembimbing|null $jabatanPembimbing
 */
class Pembimbing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pembimbing_user_id',
        'nama_guru',
        'nip',
        'jenis_kelamin',
        'alamat',
        'no_hp',
        'jabatan_pembimbing_id',
    ];

    public function jabatanPembimbing()
    {
        return $this->belongsTo(JabatanPembimbing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(PembimbingUser::class, 'pembimbing_user_id');
    }

    public function monitorings()
    {
        return $this->hasMany(Monitoring::class);
    }

    public function penempatan_pkl()
    {
        return $this->hasMany(PenempatanPKL::class);
    }
}
