<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $siswa_user_id
 * @property int|null $kelas_id
 * @property string|null $nisn
 * @property string|null $nipd
 * @property string|null $nama
 * @property string|null $tempat_lahir
 * @property \Illuminate\Support\Carbon|null $tanggal_lahir
 * @property string|null $jenis_kelamin
 * @property string|null $agama
 * @property string|null $no_hp
 * @property string|null $alamat
 * @property string|null $rt
 * @property string|null $rw
 * @property string|null $dusun
 * @property string|null $kelurahan
 * @property string|null $kecamatan
 * @property-read \App\Models\Kelas|null $kelas
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\SiswaUser|null $account
 */
class Siswa extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'user_account_enabled',
        'siswa_user_id',
        'kelas_id',
        'nisn',
        'nipd',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'no_hp',
        'alamat',
        'rt',
        'rw',
        'dusun',
        'kelurahan',
        'kecamatan',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(SiswaUser::class, 'siswa_user_id');
    }

    public function suratPengantars()
    {
        return $this->belongsToMany(SuratPengantar::class, 'surat_pengantar_siswa');
    }

    public function monitoringDetails()
    {
        return $this->hasMany(MonitoringDetail::class);
    }

    public function pembekalanAttendances()
    {
        return $this->hasMany(PembekalanAttendance::class);
    }

    public function penempatan()
    {
        return $this->hasOne(PenempatanPKL::class)->latest();
    }

    public function karir()
    {
        return $this->hasOne(SiswaKarir::class);
    }

    public function siswaKarir()
    {
        return $this->hasOne(SiswaKarir::class);
    }

    public function jurusan()
    {
        return $this->hasOneThrough(Jurusan::class, Kelas::class, 'id', 'id', 'kelas_id', 'jurusan_id');
    }

    public function mutasis()
    {
        return $this->hasMany(MutasiPKL::class, 'siswa_id');
    }
}
