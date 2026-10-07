<?php

namespace App\Exports;

use App\Models\Monitoring;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonitoringExport implements WithMultipleSheets
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            'Data Monitoring' => new MonitoringSummarySheet($this->filters),
            'Pelanggaran Siswa' => new MonitoringDetailSheet($this->filters),
        ];
    }
}

class MonitoringSummarySheet implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Monitoring::query()
            ->with(['pembimbing.user', 'bengkel'])
            ->select('monitorings.*')
            ->join(
                DB::raw('(SELECT id, ROW_NUMBER() OVER (PARTITION BY bengkel_id, pembimbing_id ORDER BY tanggal_kunjungan, id) AS monitoring_ke FROM monitorings) as sub'),
                'sub.id', '=', 'monitorings.id'
            )
            ->selectRaw('sub.monitoring_ke')
            ->withCount(['details as pelanggaran_count' => function ($q) {
                $q->where('is_pelanggaran', true);
            }]);

        if (! empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('bengkel', function ($q) use ($search) {
                    $q->where('nama_bengkel', 'like', "%{$search}%");
                })
                    ->orWhereHas('pembimbing', function ($q) use ($search) {
                        $q->where('nama_guru', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($qu) use ($search) {
                                $qu->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if (! empty($this->filters['pembimbing_id'])) {
            $query->where('pembimbing_id', $this->filters['pembimbing_id']);
        }

        if (! empty($this->filters['bengkel_id'])) {
            $query->where('bengkel_id', $this->filters['bengkel_id']);
        }

        if (! empty($this->filters['session'])) {
            $s = (int) $this->filters['session'];
            $query->where(function ($q) use ($s) {
                $q->where('monitorings.session', $s)
                  ->orWhere(function ($sub) use ($s) {
                      $sub->whereNull('monitorings.session');
                      if ($s === 1) {
                          $sub->whereRaw('DAY(tanggal_kunjungan) <= 15');
                      } else {
                          $sub->whereRaw('DAY(tanggal_kunjungan) > 15');
                      }
                  });
            });
        }

        if (! empty($this->filters['bulan'])) {
            $b = (int) $this->filters['bulan'];
            $periodSetting = \App\Models\MonitoringSetting::getActiveSetting();
            $period = collect($periodSetting->monthly_periods)->firstWhere('month', $b);
            if ($period && ! empty($period['start_date']) && ! empty($period['end_date'])) {
                $query->whereBetween('tanggal_kunjungan', [$period['start_date'], $period['end_date']]);
            }
        }

        if (! empty($this->filters['monitoring_ke'])) {
            $query->where('sub.monitoring_ke', $this->filters['monitoring_ke']);
        }

        if (! empty($this->filters['status'])) {
            if ($this->filters['status'] === 'pelanggaran') {
                $query->whereHas('details', function ($q) {
                    $q->where('is_pelanggaran', true);
                });
            } elseif ($this->filters['status'] === 'diterima') {
                $query->where('laporan_diterima', true);
            } elseif ($this->filters['status'] === 'menunggu') {
                $query->where('laporan_diterima', false);
            }
        }

        return $query->orderBy('tanggal_kunjungan', 'desc');
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Kunjungan',
            'Sesi Monitoring',
            'Bulan',
            'Pembimbing',
            'Bengkel',
            'Catatan',
            'Jumlah Pelanggaran',
            'Status Laporan',
        ];
    }

    public function map($row): array
    {
        $status = $row->laporan_diterima ? 'Diterima' : 'Menunggu';
        $vDate = $row->tanggal_kunjungan ? \Carbon\Carbon::parse($row->tanggal_kunjungan) : null;
        $sessionNum = $row->session ? (int) $row->session : ($vDate && $vDate->day <= 15 ? 1 : 2);
        $mInfo = $vDate ? \App\Models\MonitoringSetting::getMonthForDate($vDate->toDateString()) : null;
        $bulanName = $mInfo['month_name'] ?? ($vDate ? $vDate->locale('id')->translatedFormat('F Y') : '-');

        return [
            $row->id,
            $vDate ? $vDate->translatedFormat('d F Y') : '-',
            'Monitoring ' . $sessionNum,
            $bulanName,
            $row->pembimbing?->nama_guru ?? $row->pembimbing?->user?->name ?? '-',
            $row->bengkel?->nama_bengkel ?? '-',
            $row->catatan_monitoring ?? '-',
            $row->pelanggaran_count,
            $status,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class MonitoringDetailSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Monitoring::query()
            ->with(['pembimbing.user', 'bengkel', 'details.siswa.kelas'])
            ->select('monitorings.*')
            ->join(
                DB::raw('(SELECT id, ROW_NUMBER() OVER (PARTITION BY bengkel_id, pembimbing_id ORDER BY tanggal_kunjungan, id) AS monitoring_ke FROM monitorings) as sub'),
                'sub.id', '=', 'monitorings.id'
            )
            ->selectRaw('sub.monitoring_ke')
            ->whereHas('details', function ($q) {
                $q->where('is_pelanggaran', true);
            });

        if (! empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('bengkel', function ($q) use ($search) {
                    $q->where('nama_bengkel', 'like', "%{$search}%");
                })
                    ->orWhereHas('pembimbing', function ($q) use ($search) {
                        $q->where('nama_guru', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($qu) use ($search) {
                                $qu->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if (! empty($this->filters['pembimbing_id'])) {
            $query->where('pembimbing_id', $this->filters['pembimbing_id']);
        }

        if (! empty($this->filters['bengkel_id'])) {
            $query->where('bengkel_id', $this->filters['bengkel_id']);
        }

        if (! empty($this->filters['session'])) {
            $s = (int) $this->filters['session'];
            $query->where(function ($q) use ($s) {
                $q->where('monitorings.session', $s)
                  ->orWhere(function ($sub) use ($s) {
                      $sub->whereNull('monitorings.session');
                      if ($s === 1) {
                          $sub->whereRaw('DAY(tanggal_kunjungan) <= 15');
                      } else {
                          $sub->whereRaw('DAY(tanggal_kunjungan) > 15');
                      }
                  });
            });
        }

        if (! empty($this->filters['bulan'])) {
            $b = (int) $this->filters['bulan'];
            $periodSetting = \App\Models\MonitoringSetting::getActiveSetting();
            $period = collect($periodSetting->monthly_periods)->firstWhere('month', $b);
            if ($period && ! empty($period['start_date']) && ! empty($period['end_date'])) {
                $query->whereBetween('tanggal_kunjungan', [$period['start_date'], $period['end_date']]);
            }
        }

        if (! empty($this->filters['monitoring_ke'])) {
            $query->where('sub.monitoring_ke', $this->filters['monitoring_ke']);
        }

        if (! empty($this->filters['status'])) {
            if ($this->filters['status'] === 'pelanggaran') {
                $query->whereHas('details', function ($q) {
                    $q->where('is_pelanggaran', true);
                });
            } elseif ($this->filters['status'] === 'diterima') {
                $query->where('laporan_diterima', true);
            } elseif ($this->filters['status'] === 'menunggu') {
                $query->where('laporan_diterima', false);
            }
        }

        $items = $query->orderBy('tanggal_kunjungan', 'desc')->get();

        $rows = [];
        $no = 1;
        foreach ($items as $row) {
            $status = $row->laporan_diterima ? 'Diterima' : 'Menunggu';
            $vDate = $row->tanggal_kunjungan ? \Carbon\Carbon::parse($row->tanggal_kunjungan) : null;
            $sessionNum = $row->session ? (int) $row->session : ($vDate && $vDate->day <= 15 ? 1 : 2);
            $mInfo = $vDate ? \App\Models\MonitoringSetting::getMonthForDate($vDate->toDateString()) : null;
            $bulanName = $mInfo['month_name'] ?? ($vDate ? $vDate->locale('id')->translatedFormat('F Y') : '-');
            $violations = $row->details->where('is_pelanggaran', true);

            foreach ($violations as $detail) {
                $rows[] = [
                    $no++,
                    $vDate ? $vDate->translatedFormat('d F Y') : '-',
                    'Monitoring ' . $sessionNum,
                    $bulanName,
                    $row->pembimbing?->nama_guru ?? $row->pembimbing?->user?->name ?? '-',
                    $row->bengkel?->nama_bengkel ?? '-',
                    $detail->siswa?->nama ?? '-',
                    $detail->siswa?->kelas?->nama_kelas ?? '-',
                    $detail->catatan ?? '-',
                    $status,
                ];
            }
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Kunjungan',
            'Sesi Monitoring',
            'Bulan',
            'Pembimbing',
            'Bengkel',
            'Nama Siswa',
            'Kelas',
            'Catatan Pelanggaran',
            'Status Laporan',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
