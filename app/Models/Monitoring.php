<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    protected $fillable = [
        'pembimbing_id',
        'bengkel_id',
        'session',
        'tanggal_kunjungan',
        'catatan_monitoring',
        'foto_kegiatan',
        'laporan_diterima',
    ];

    protected $casts = [
        'session' => 'integer',
        'tanggal_kunjungan' => 'date',
        'laporan_diterima' => 'boolean',
    ];

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class);
    }

    public function bengkel()
    {
        return $this->belongsTo(Bengkel::class);
    }

    public function details()
    {
        return $this->hasMany(MonitoringDetail::class);
    }
}
