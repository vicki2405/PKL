<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RekapKarirExport;
use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\KarirSetting;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaKarir;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class RekapKarirController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $tipeKarir = $request->input('tipe_karir', 'all');
        $kelasId = $request->input('kelas_id');
        $jurusanId = $request->input('jurusan_id');

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 20, 50, 100, 200])) {
            $perPage = 10;
        }

        $query = $this->buildQuery($request);

        $siswaList = $query->paginate($perPage)->withQueryString();

        // Compute Live Stats scoped by active filters (except tipe_karir so pill counters stay useful)
        $baseStatsQuery = Siswa::query();
        if ($kelasId) {
            $baseStatsQuery->where('kelas_id', $kelasId);
        }
        if ($jurusanId) {
            $baseStatsQuery->whereHas('kelas', fn ($q) => $q->where('jurusan_id', $jurusanId));
        }
        if ($search !== '') {
            $baseStatsQuery->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nipd', 'like', "%{$search}%")
                    ->orWhereHas('kelas', fn ($k) => $k->where('nama_kelas', 'like', "%{$search}%"))
                    ->orWhereHas('siswaKarir', function ($k) use ($search) {
                        $k->where('nama_universitas', 'like', "%{$search}%")
                            ->orWhere('program_studi', 'like', "%{$search}%")
                            ->orWhere('nama_perusahaan', 'like', "%{$search}%")
                            ->orWhere('posisi_kerja', 'like', "%{$search}%")
                            ->orWhere('lokasi_kerja', 'like', "%{$search}%")
                            ->orWhere('nama_usaha', 'like', "%{$search}%");
                    });
            });
        }

        $statsResult = (clone $baseStatsQuery)
            ->leftJoin('siswa_karirs', 'siswas.id', '=', 'siswa_karirs.siswa_id')
            ->selectRaw("
                COUNT(siswas.id) as total_siswa,
                SUM(CASE WHEN siswa_karirs.tipe_karir = 'kuliah' THEN 1 ELSE 0 END) as total_kuliah,
                SUM(CASE WHEN siswa_karirs.tipe_karir = 'kerja' THEN 1 ELSE 0 END) as total_kerja,
                SUM(CASE WHEN siswa_karirs.tipe_karir = 'wirausaha' THEN 1 ELSE 0 END) as total_wirausaha
            ")
            ->first();

        $totalSiswa = (int) ($statsResult->total_siswa ?? 0);
        $totalKuliah = (int) ($statsResult->total_kuliah ?? 0);
        $totalKerja = (int) ($statsResult->total_kerja ?? 0);
        $totalWirausaha = (int) ($statsResult->total_wirausaha ?? 0);
        $totalMengisi = $totalKuliah + $totalKerja + $totalWirausaha;
        $totalBelum = max(0, $totalSiswa - $totalMengisi);

        $stats = [
            'total_siswa' => $totalSiswa,
            'total_mengisi' => $totalMengisi,
            'total_kuliah' => $totalKuliah,
            'total_kerja' => $totalKerja,
            'total_wirausaha' => $totalWirausaha,
            'total_belum' => $totalBelum,
        ];

        $kelases = Cache::remember('options.kelas_karir', 86400, fn () => Kelas::select('id', 'nama_kelas', 'jurusan_id')->orderBy('nama_kelas')->get());
        $jurusans = Cache::remember('options.jurusan_karir', 86400, fn () => Jurusan::select('id', 'nama_jurusan')->orderBy('nama_jurusan')->get());

        return Inertia::render('Admin/RekapKarir/Index', [
            'siswaList' => $siswaList,
            'stats' => $stats,
            'kelases' => $kelases,
            'jurusans' => $jurusans,
            'portalSetting' => KarirSetting::getSetting(),
            'filters' => [
                'search' => $search,
                'tipe_karir' => $tipeKarir,
                'kelas_id' => $kelasId,
                'jurusan_id' => $jurusanId,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function toggleStatus(Request $request)
    {
        $pesan = $request->input('pesan_penutupan');
        $userId = $request->user()?->id;

        $setting = KarirSetting::toggleStatus($userId, $pesan);

        $statusText = $setting->is_open ? 'dibuka' : 'ditutup';

        return redirect()->back()->with('message', "Pengisian formulir rencana karier untuk siswa berhasil {$statusText}.");
    }

    public function update(Request $request, Siswa $siswa)
    {
        $request->validate([
            'tipe_karir' => 'required|in:kuliah,kerja,wirausaha',
        ]);

        $tipe = $request->tipe_karir;
        $rules = ['tipe_karir' => 'required'];

        if ($tipe === 'kuliah') {
            $rules = array_merge($rules, [
                'nama_universitas' => 'required|string|max:150',
                'status_universitas' => 'required|in:PTN,PTS,Kedinasan,Luar Negeri',
                'jenjang_studi' => 'required|in:D4,S1',
                'program_studi' => 'required|string|max:100',
                'jalur_masuk' => 'required|in:SNBP,SNBT,Mandiri,Beasiswa,Lainnya',
                'tahun_mulai' => 'required|integer|min:'.(now()->year - 2).'|max:'.(now()->year + 5),
            ]);
        } elseif ($tipe === 'kerja') {
            $rules = array_merge($rules, [
                'nama_perusahaan' => 'required|string|max:150',
                'posisi_kerja' => 'required|string|max:100',
                'jenis_pekerjaan' => 'required|in:Kontrak,Tetap,Magang,Freelance',
                'kesesuaian_jurusan' => 'required|in:Sangat Sesuai,Sesuai,Kurang Sesuai,Tidak Sesuai',
                'target_mulai_kerja' => 'required|in:Segera,1-3 Bulan,3-6 Bulan,Lainnya',
                'ekspektasi_gaji' => 'required|in:< UMK,Setara UMK,> UMK',
                'lokasi_kerja' => 'required|string|max:250',
            ]);
        } elseif ($tipe === 'wirausaha') {
            $rules = array_merge($rules, [
                'nama_usaha' => 'required|string|max:150',
                'sektor_usaha' => 'required|in:Jasa,Manufaktur,Kuliner,Kreatif,Perdagangan,Lainnya',
                'status_kepemilikan' => 'required|in:Mandiri,Keluarga,Kemitraan',
                'modal_awal' => 'required|in:< 5 Juta,5 - 20 Juta,> 20 Juta',
                'lokasi_usaha' => 'required|string|max:250',
            ]);
        }

        $validatedData = $request->validate($rules);

        // Reset fields of other paths to NULL to keep data clean
        $payload = array_merge([
            // Reset Kuliah
            'nama_universitas' => null, 'status_universitas' => null, 'jenjang_studi' => null,
            'program_studi' => null, 'jalur_masuk' => null, 'tahun_mulai' => null,
            // Reset Kerja
            'nama_perusahaan' => null, 'posisi_kerja' => null, 'jenis_pekerjaan' => null,
            'kesesuaian_jurusan' => null, 'target_mulai_kerja' => null, 'ekspektasi_gaji' => null,
            'lokasi_kerja' => null,
            // Reset Wirausaha
            'nama_usaha' => null, 'sektor_usaha' => null, 'status_kepemilikan' => null,
            'modal_awal' => null, 'lokasi_usaha' => null,
        ], $validatedData);

        SiswaKarir::updateOrCreate(
            ['siswa_id' => $siswa->id],
            $payload
        );

        self::clearPdfCache();

        return redirect()->back()->with('message', 'Aspirasi karier siswa berhasil diperbarui oleh Admin.');
    }

    public static function clearPdfCache(): void
    {
        Cache::store('file')->forget('rekap_karir_pdf_all');
        Cache::store('file')->forget('rekap_karir_pdf_filter_hashes');
    }

    public function exportExcel(Request $request)
    {
        $tipeKarir = $request->input('tipe_karir', 'all');
        $data = $this->buildQuery($request)->get();
        $suffix = $tipeKarir !== 'all' ? "_{$tipeKarir}" : '';
        $fileName = 'rekap_aspirasi_karier' . $suffix . '_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new RekapKarirExport($data, $tipeKarir), $fileName);
    }

    public function exportPdf(Request $request)
    {
        $tipeKarir = $request->input('tipe_karir', 'all');
        $filterKey = md5(json_encode($request->only(['search', 'tipe_karir', 'jurusan_id', 'kelas_id'])));
        $cacheKey = 'rekap_karir_pdf_' . $filterKey;
        $fileCache = Cache::store('file');

        $pdfOutput = $fileCache->remember($cacheKey, now()->addMinutes(30), function () use ($request, $tipeKarir) {
            $data = $this->buildQuery($request)->get();

            $totalSiswa = $data->count();
            $totalKuliah = $data->filter(fn ($s) => $s->siswaKarir?->tipe_karir === 'kuliah')->count();
            $totalKerja = $data->filter(fn ($s) => $s->siswaKarir?->tipe_karir === 'kerja')->count();
            $totalWirausaha = $data->filter(fn ($s) => $s->siswaKarir?->tipe_karir === 'wirausaha')->count();
            $totalBelum = $data->filter(fn ($s) => is_null($s->siswaKarir))->count();

            $stats = [
                'total_siswa' => $totalSiswa,
                'total_kuliah' => $totalKuliah,
                'total_kerja' => $totalKerja,
                'total_wirausaha' => $totalWirausaha,
                'total_belum' => $totalBelum,
            ];

            $filterParts = [];
            if ($request->filled('tipe_karir') && $request->input('tipe_karir') !== 'all') {
                $filterParts[] = 'Tipe: ' . ucfirst($request->input('tipe_karir'));
            }
            if ($request->filled('jurusan_id')) {
                $jurusan = Jurusan::find($request->input('jurusan_id'));
                if ($jurusan) {
                    $filterParts[] = 'Jurusan: ' . $jurusan->nama_jurusan;
                }
            }
            if ($request->filled('kelas_id')) {
                $kelas = Kelas::find($request->input('kelas_id'));
                if ($kelas) {
                    $filterParts[] = 'Kelas: ' . $kelas->nama_kelas;
                }
            }
            if ($request->filled('search')) {
                $filterParts[] = 'Pencarian: "' . $request->input('search') . '"';
            }

            $filterLabel = !empty($filterParts) ? implode(' | ', $filterParts) : 'Semua Siswa';

            return Pdf::loadView('pdf.rekap_karir', [
                'data' => $data,
                'stats' => $stats,
                'date' => now()->translatedFormat('d F Y'),
                'filterLabel' => $filterLabel,
                'tipeKarir' => $tipeKarir,
            ])->setPaper('a4', 'landscape')->output();
        });

        $suffix = $tipeKarir !== 'all' ? "_{$tipeKarir}" : '';
        $fileName = 'rekap_aspirasi_karier' . $suffix . '_' . now()->format('Y-m-d') . '.pdf';

        return response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'no-store',
        ]);
    }

    private function buildQuery(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $tipeKarir = $request->input('tipe_karir', 'all');
        $kelasId = $request->input('kelas_id');
        $jurusanId = $request->input('jurusan_id');

        $query = Siswa::query()
            ->with([
                'kelas.jurusan',
                'siswaKarir',
                'user',
                'account',
                'penempatan.bengkel',
                'penempatan.pembimbing',
            ])
            ->orderBy('nama', 'asc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nipd', 'like', "%{$search}%")
                    ->orWhereHas('kelas', fn ($k) => $k->where('nama_kelas', 'like', "%{$search}%"))
                    ->orWhereHas('siswaKarir', function ($k) use ($search) {
                        $k->where('nama_universitas', 'like', "%{$search}%")
                            ->orWhere('program_studi', 'like', "%{$search}%")
                            ->orWhere('nama_perusahaan', 'like', "%{$search}%")
                            ->orWhere('posisi_kerja', 'like', "%{$search}%")
                            ->orWhere('lokasi_kerja', 'like', "%{$search}%")
                            ->orWhere('nama_usaha', 'like', "%{$search}%");
                    });
            });
        }

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        if ($jurusanId) {
            $query->whereHas('kelas', function ($q) use ($jurusanId) {
                $q->where('jurusan_id', $jurusanId);
            });
        }

        if ($tipeKarir && $tipeKarir !== 'all') {
            if ($tipeKarir === 'belum') {
                $query->doesntHave('siswaKarir');
            } else {
                $query->whereHas('siswaKarir', function ($q) use ($tipeKarir) {
                    $q->where('tipe_karir', $tipeKarir);
                });
            }
        }

        return $query;
    }
}
