<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonitoringDetail extends Model
{
    protected $fillable = [
        'monitoring_id',
        'siswa_id',
        'catatan',
        'is_pelanggaran',
        'hadir',
        'izin',
        'sakit',
        'alpha',
    ];

    protected $casts = [
        'is_pelanggaran' => 'boolean',
    ];

    public function monitoring()
    {
        return $this->belongsTo(Monitoring::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
