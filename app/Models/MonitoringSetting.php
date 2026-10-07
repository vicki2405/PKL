<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class MonitoringSetting extends Model
{
    protected $table = 'monitoring_settings';

    protected $fillable = [
        'tanggal_mulai_pkl',
        'tanggal_mulai_monitoring',
        'tanggal_selesai_pkl',
        'periode_mode',
        'monthly_periods',
    ];

    protected $casts = [
        'tanggal_mulai_pkl' => 'date:Y-m-d',
        'tanggal_mulai_monitoring' => 'date:Y-m-d',
        'tanggal_selesai_pkl' => 'date:Y-m-d',
        'monthly_periods' => 'array',
    ];

    /**
     * Mendapatkan setting aktif atau default jika belum ada di database.
     */
    public static function getActiveSetting(): self
    {
        return Cache::remember('monitoring.period_setting', 3600, function () {
            // Cek apakah tabel ada di database
            if (Schema::hasTable('monitoring_settings')) {
                $setting = self::query()->first();
                if ($setting) {
                    if (empty($setting->tanggal_mulai_monitoring)) {
                        $setting->tanggal_mulai_monitoring = $setting->tanggal_mulai_pkl;
                    }
                    return $setting;
                }
            }

            // Cek apakah ada default di surat_pengantar_template_settings
            $defaultDate = null;
            if (Schema::hasTable('surat_pengantar_template_settings')) {
                $template = SuratPengantarTemplateSetting::query()->first();
                if ($template && ! empty($template->default_tanggal_mulai)) {
                    $defaultDate = $template->default_tanggal_mulai;
                }
            }

            // Jika belum ada, default ke awal bulan saat ini atau bulan lalu
            if (! $defaultDate) {
                $defaultDate = now()->startOfMonth()->toDateString();
            }

            $model = new self([
                'tanggal_mulai_pkl' => $defaultDate,
                'tanggal_mulai_monitoring' => $defaultDate,
                'periode_mode' => 'calendar',
            ]);
            $model->monthly_periods = self::generatePeriodsFromStart($defaultDate, 'calendar', $defaultDate);

            return $model;
        });
    }

    /**
     * Menghitung tanggal efektif mulai monitoring.
     * Jika tanggal mulai PKL jatuh pada tanggal 16 ke atas (misal tgl 20),
     * sisa hari di bulan tersebut tidak cukup untuk 2x kunjungan (2 minggu sekali).
     * Maka jadwal monitoring Bulan Ke-1 otomatis dimulai awal bulan berikutnya!
     */
    public static function calculateEffectiveMonitoringStart(string $startDate, ?string $monitoringStartDate = null): string
    {
        if (! empty($monitoringStartDate)) {
            return Carbon::parse($monitoringStartDate)->toDateString();
        }

        $dt = Carbon::parse($startDate);
        // Khusus jika mulai pertengahan Juni (tgl 16 ke atas), karena Juli libur sekolah/semester -> mulai 1 Agustus
        if ($dt->month === 6 && $dt->day >= 16) {
            return Carbon::create($dt->year, 8, 1)->toDateString();
        }

        if ($dt->day >= 16) {
            return $dt->copy()->addMonthNoOverflow()->startOfMonth()->toDateString();
        }

        return $dt->startOfMonth()->toDateString();
    }

    /**
     * Menghasilkan array periode bulan PKL berdasarkan tanggal mulai dan selesai PKL.
     * Mode: 'calendar' (bulan kalender) atau 'cycle' (siklus 30 hari).
     * Setiap bulan dijadwalkan 2x monitoring (2 minggu sekali):
     * Sesi 1: Minggu 1-2 (Tgl 1 s.d. 15)
     * Sesi 2: Minggu 3-4 (Tgl 16 s.d. Akhir Bulan / Tanggal Selesai)
     */
    public static function generatePeriodsFromStart(string $startDate, string $mode = 'calendar', ?string $monitoringStartDate = null, ?string $endDate = null): array
    {
        $effectiveStart = self::calculateEffectiveMonitoringStart($startDate, $monitoringStartDate);
        $start = Carbon::parse($effectiveStart)->startOfDay();

        $totalMonths = 6;
        $endCarbon = null;
        if (! empty($endDate)) {
            $endCarbon = Carbon::parse($endDate)->endOfDay();
            $diff = ($endCarbon->year - $start->year) * 12 + ($endCarbon->month - $start->month) + 1;
            if ($diff > 0 && $diff <= 12) {
                $totalMonths = $diff;
            }
        }

        $periods = [];

        for ($i = 0; $i < $totalMonths; $i++) {
            if ($mode === 'calendar') {
                $mStart = $start->copy()->addMonthsNoOverflow($i)->startOfMonth();
                if ($i === 0 && Carbon::parse($effectiveStart)->gt($mStart)) {
                    $mStart = Carbon::parse($effectiveStart)->startOfDay();
                }

                $mEnd = $mStart->copy()->endOfMonth();
                if ($i === $totalMonths - 1 && $endCarbon && $endCarbon->lt($mEnd)) {
                    $mEnd = $endCarbon->copy();
                }

                $monthName = $mStart->locale('id')->translatedFormat('F Y');

                // Sesi 1: Tgl 1 s.d. 15 (atau sampai mEnd jika mEnd <= 15)
                $s1Start = $mStart->toDateString();
                $s1EndDay = min(15, (int) $mEnd->day);
                $s1End = $mStart->copy()->day($s1EndDay)->toDateString();

                // Sesi 2: Tgl 16 s.d. Akhir Bulan / Akhir PKL
                if ($mEnd->day > 15) {
                    $s2Start = $mStart->copy()->day(16)->toDateString();
                    $s2End = $mEnd->toDateString();
                } else {
                    $s2Start = $s1End;
                    $s2End = $s1End;
                }
            } else {
                // Berbasis siklus 30 hari
                $mStart = $start->copy()->addMonths($i);
                $mEnd = $mStart->copy()->addMonth()->subDay()->endOfDay();
                if ($i === $totalMonths - 1 && $endCarbon && $endCarbon->lt($mEnd)) {
                    $mEnd = $endCarbon->copy();
                }
                $monthName = $mStart->locale('id')->translatedFormat('d M') . ' - ' . $mEnd->locale('id')->translatedFormat('d M Y');

                $s1Start = $mStart->toDateString();
                $s1End = $mStart->copy()->addDays(min(14, (int) $mStart->diffInDays($mEnd)))->toDateString();

                $s2Start = Carbon::parse($s1End)->copy()->addDay()->toDateString();
                $s2End = $mEnd->toDateString();
                if (Carbon::parse($s2Start)->gt(Carbon::parse($s2End))) {
                    $s2Start = $s1End;
                }
            }

            $s1PeriodText = Carbon::parse($s1Start)->locale('id')->translatedFormat('d M Y') . ' - ' . Carbon::parse($s1End)->locale('id')->translatedFormat('d M Y');
            $s2PeriodText = Carbon::parse($s2Start)->locale('id')->translatedFormat('d M Y') . ' - ' . Carbon::parse($s2End)->locale('id')->translatedFormat('d M Y');

            $periods[] = [
                'month' => $i + 1,
                'label' => 'Bulan ' . ($i + 1),
                'month_name' => $monthName,
                'start_date' => $mStart->toDateString(),
                'end_date' => $mEnd->toDateString(),
                'period_text' => Carbon::parse($mStart->toDateString())->locale('id')->translatedFormat('d M Y') . ' - ' . Carbon::parse($mEnd->toDateString())->locale('id')->translatedFormat('d M Y'),
                'target_monitoring' => 2,
                'session_1' => [
                    'session' => 1,
                    'label' => 'Monitoring 1 (Minggu 1-2)',
                    'start_date' => $s1Start,
                    'end_date' => $s1End,
                    'period_text' => $s1PeriodText,
                ],
                'session_2' => [
                    'session' => 2,
                    'label' => 'Monitoring 2 (Minggu 3-4)',
                    'start_date' => $s2Start,
                    'end_date' => $s2End,
                    'period_text' => $s2PeriodText,
                ],
            ];
        }

        return $periods;
    }

    /**
     * Mendapatkan periode bulan untuk tanggal tertentu (1..6) beserta sesi aktif (1 atau 2).
     */
    public static function getMonthForDate(string $date): array
    {
        $setting = self::getActiveSetting();
        $periods = $setting->monthly_periods ?: self::generatePeriodsFromStart(
            (string) ($setting->tanggal_mulai_pkl ?? now()->toDateString()),
            $setting->periode_mode ?? 'calendar',
            (string) ($setting->tanggal_mulai_monitoring ?? $setting->tanggal_mulai_pkl ?? now()->toDateString()),
            $setting->tanggal_selesai_pkl ? (string) $setting->tanggal_selesai_pkl : null
        );

        $target = Carbon::parse($date)->startOfDay();

        foreach ($periods as $p) {
            $pStart = Carbon::parse($p['start_date'])->startOfDay();
            $pEnd = Carbon::parse($p['end_date'])->endOfDay();

            if ($target->between($pStart, $pEnd)) {
                return self::enrichPeriodWithSession($p, $target);
            }
        }

        // Jika tanggal sebelum periode 1, masukkan ke Bulan 1
        $first = $periods[0] ?? null;
        if ($first && $target->lessThan(Carbon::parse($first['start_date']))) {
            return self::enrichPeriodWithSession($first, $target);
        }

        // Jika tanggal setelah periode 6, masukkan ke Bulan 6
        $last = $periods[count($periods) - 1] ?? null;
        if ($last && $target->greaterThan(Carbon::parse($last['end_date']))) {
            return self::enrichPeriodWithSession($last, $target);
        }

        $fallback = $first ?: [
            'month' => 1,
            'label' => 'Bulan 1',
            'month_name' => now()->locale('id')->translatedFormat('F Y'),
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'period_text' => now()->startOfMonth()->locale('id')->translatedFormat('d M Y') . ' - ' . now()->endOfMonth()->locale('id')->translatedFormat('d M Y'),
            'target_monitoring' => 2,
        ];

        return self::enrichPeriodWithSession($fallback, $target);
    }

    /**
     * Menambahkan informasi sesi monitoring (Sesi 1 atau Sesi 2) ke objek periode.
     */
    private static function enrichPeriodWithSession(array $period, Carbon $targetDate): array
    {
        $session1 = $period['session_1'] ?? null;
        $session2 = $period['session_2'] ?? null;

        $sessionNumber = 1;
        $sessionLabel = 'Monitoring Ke-1 (Minggu 1-2)';
        $sessionPeriodText = $session1['period_text'] ?? '';

        if ($session2 && ! empty($session2['start_date'])) {
            $s2Start = Carbon::parse($session2['start_date'])->startOfDay();
            if ($targetDate->greaterThanOrEqualTo($s2Start)) {
                $sessionNumber = 2;
                $sessionLabel = 'Monitoring Ke-2 (Minggu 3-4)';
                $sessionPeriodText = $session2['period_text'] ?? '';
            }
        } elseif ($targetDate->day > 15) {
            $sessionNumber = 2;
            $sessionLabel = 'Monitoring Ke-2 (Minggu 3-4)';
        }

        return array_merge($period, [
            'current_session' => $sessionNumber,
            'session_label' => $sessionLabel,
            'session_period_text' => $sessionPeriodText,
        ]);
    }
}
