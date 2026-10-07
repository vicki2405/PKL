<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bengkel;
use App\Models\Kelas;
use App\Models\PenempatanPKL;
use App\Models\Siswa;
use App\Models\SuratPengantar;
use App\Models\SuratPengantarTemplateSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SuratPengantarController extends Controller
{
    public function index(Request $request)
    {
        $query = SuratPengantar::query();

        if ($request->search) {
            $query->where('nomor_surat', 'like', '%'.$request->search.'%')
                ->orWhere('tujuan_surat', 'like', '%'.$request->search.'%');
        }

        $suratPengantars = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Cetak/SuratPengantar/Index', [
            'suratPengantars' => $suratPengantars,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        $templateSetting = SuratPengantarTemplateSetting::query()->first();

        $kelasOptions = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $bengkelOptions = Bengkel::query()->select(['id', 'nama_bengkel', 'dusun', 'desa', 'kecamatan', 'deskripsi'])
            ->orderBy('nama_bengkel')
            ->get();

        $siswaOptions = Siswa::query()->select(['id', 'nama', 'kelas_id', 'nisn'])
            ->with(['kelas:id,nama_kelas,jurusan_id', 'kelas.jurusan:id,nama_jurusan'])
            ->orderBy('nama')
            ->get()
            ->map(function ($siswa) {
                return [
                    'id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'kelas_id' => $siswa->kelas_id,
                    'nisn' => $siswa->nisn,
                    'kelas_nama' => $siswa->kelas->nama_kelas ?? '-',
                    'jurusan_nama' => $siswa->kelas->jurusan->nama_jurusan ?? '-',
                ];
            });

        $bengkelStudents = PenempatanPKL::query()
            ->join('siswas', 'penempatan_p_k_l_s.siswa_id', '=', 'siswas.id')
            ->leftJoin('kelas', 'siswas.kelas_id', '=', 'kelas.id')
            ->leftJoin('jurusans', 'kelas.jurusan_id', '=', 'jurusans.id')
            ->select([
                'penempatan_p_k_l_s.bengkel_id',
                'siswas.id',
                'siswas.nama',
                'siswas.nisn',
                'siswas.kelas_id',
                'kelas.nama_kelas',
                'jurusans.nama_jurusan',
            ])
            ->get()
            ->groupBy('bengkel_id')
            ->map(function ($items) {
                return $items->map(fn ($item) => [
                    'id' => (int) $item->id,
                    'nama' => (string) $item->nama,
                    'nisn' => (string) ($item->nisn ?? ''),
                    'kelas_id' => (int) $item->kelas_id,
                    'kelas_nama' => (string) ($item->nama_kelas ?? '-'),
                    'jurusan_nama' => (string) ($item->nama_jurusan ?? '-'),
                ])->values()->toArray();
            })
            ->toArray();

        return Inertia::render('Admin/Cetak/SuratPengantar/Create', [
            'kelasOptions' => $kelasOptions,
            'siswaOptions' => $siswaOptions,
            'bengkelOptions' => $bengkelOptions,
            'bengkelStudents' => $bengkelStudents,
            'templateSettings' => [
                'default_nomor_surat' => $templateSetting->default_nomor_surat ?? '',
                'default_lampiran' => $templateSetting->default_lampiran ?? '',
                'default_perihal' => $templateSetting->default_perihal ?? '',
                'default_tanggal_mulai' => $templateSetting->default_tanggal_mulai ?? '',
                'default_tanggal_selesai' => $templateSetting->default_tanggal_selesai ?? '',
                'kepala_sekolah_nama' => $templateSetting->kepala_sekolah_nama ?? '',
                'kepala_sekolah_nbm' => $templateSetting->kepala_sekolah_nbm ?? '',
                'ketua_pkl_nama' => $templateSetting->ketua_pkl_nama ?? '',
                'ketua_pkl_nbm' => $templateSetting->ketua_pkl_nbm ?? '',
                'kop_surat_url' => $templateSetting?->kop_surat_path ? Storage::url($templateSetting->kop_surat_path) : null,
                'kepala_sekolah_ttd_url' => $templateSetting?->kepala_sekolah_ttd_path ? Storage::url($templateSetting->kepala_sekolah_ttd_path) : null,
                'kepala_sekolah_stempel_url' => $templateSetting?->kepala_sekolah_stempel_path ? Storage::url($templateSetting->kepala_sekolah_stempel_path) : null,
                'ketua_pkl_ttd_url' => $templateSetting?->ketua_pkl_ttd_path ? Storage::url($templateSetting->ketua_pkl_ttd_path) : null,
                'ketua_pkl_stempel_url' => $templateSetting?->ketua_pkl_stempel_path ? Storage::url($templateSetting->ketua_pkl_stempel_path) : null,
            ],
        ]);
    }

    public function saveTemplateSettings(Request $request)
    {
        $validated = $request->validate([
            'default_nomor_surat' => 'nullable|string|max:255',
            'default_lampiran' => 'nullable|string|max:255',
            'default_perihal' => 'nullable|string|max:255',
            'default_tanggal_mulai' => 'nullable|date_format:Y-m-d',
            'default_tanggal_selesai' => 'nullable|date_format:Y-m-d|after_or_equal:default_tanggal_mulai',
            'kepala_sekolah_nama' => 'nullable|string|max:255',
            'kepala_sekolah_nbm' => 'nullable|string|max:255',
            'ketua_pkl_nama' => 'nullable|string|max:255',
            'ketua_pkl_nbm' => 'nullable|string|max:255',
            'kop_surat' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'kepala_sekolah_ttd' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'kepala_sekolah_stempel' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'ketua_pkl_ttd' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'ketua_pkl_stempel' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $template = SuratPengantarTemplateSetting::query()->firstOrCreate(
                ['id' => 1],
                ['id' => 1]
            );

            $template->fill([
                'default_nomor_surat' => $validated['default_nomor_surat'] ?? null,
                'default_lampiran' => $validated['default_lampiran'] ?? null,
                'default_perihal' => $validated['default_perihal'] ?? null,
                'default_tanggal_mulai' => $validated['default_tanggal_mulai'] ?? null,
                'default_tanggal_selesai' => $validated['default_tanggal_selesai'] ?? null,
                'kepala_sekolah_nama' => $validated['kepala_sekolah_nama'] ?? null,
                'kepala_sekolah_nbm' => $validated['kepala_sekolah_nbm'] ?? null,
                'ketua_pkl_nama' => $validated['ketua_pkl_nama'] ?? null,
                'ketua_pkl_nbm' => $validated['ketua_pkl_nbm'] ?? null,
            ]);

            $uploadMapping = [
                'kop_surat' => 'kop_surat_path',
                'kepala_sekolah_ttd' => 'kepala_sekolah_ttd_path',
                'kepala_sekolah_stempel' => 'kepala_sekolah_stempel_path',
                'ketua_pkl_ttd' => 'ketua_pkl_ttd_path',
                'ketua_pkl_stempel' => 'ketua_pkl_stempel_path',
            ];

            foreach ($uploadMapping as $requestKey => $dbColumn) {
                if ($request->hasFile($requestKey)) {
                    $template->{$dbColumn} = $request->file($requestKey)->store('berkas/surat-pengantar', 'public');
                }
            }

            $template->save();
        });

        return redirect()->back()->with('message', 'Setting template surat berhasil disimpan.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_surat' => 'required|string|max:255',
            'lampiran' => 'nullable|string|max:255',
            'tujuan_surat' => 'required|string|max:255',
            'alamat_tujuan' => 'required|string',
            'tanggal_mulai' => 'required|date_format:Y-m-d',
            'tanggal_selesai' => 'required|date_format:Y-m-d|after_or_equal:tanggal_mulai',
            'tanggal_surat' => 'required|date_format:Y-m-d',
            'kepala_sekolah_nama' => 'required|string|max:255',
            'kepala_sekolah_nbm' => 'nullable|string|max:255',
            'ketua_pkl_nama' => 'required|string|max:255',
            'ketua_pkl_nbm' => 'nullable|string|max:255',
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswas,id',
        ]);

        DB::transaction(function () use ($validated) {
            $surat = SuratPengantar::create([
                'nomor_surat' => $validated['nomor_surat'],
                'lampiran' => $validated['lampiran'],
                'tujuan_surat' => $validated['tujuan_surat'],
                'alamat_tujuan' => $validated['alamat_tujuan'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'tanggal_surat' => $validated['tanggal_surat'],
                'kepala_sekolah_nama' => $validated['kepala_sekolah_nama'],
                'kepala_sekolah_nbm' => $validated['kepala_sekolah_nbm'],
                'ketua_pkl_nama' => $validated['ketua_pkl_nama'],
                'ketua_pkl_nbm' => $validated['ketua_pkl_nbm'],
            ]);

            $surat->siswas()->sync($validated['siswa_ids']);
        });

        return redirect()->route('admin.surat.pengantar.index')->with('message', 'Surat Pengantar berhasil dibuat.');
    }

    public function edit(SuratPengantar $suratPengantar)
    {
        $suratPengantar->load('siswas');

        $kelasOptions = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $bengkelOptions = Bengkel::query()->select(['id', 'nama_bengkel', 'dusun', 'desa', 'kecamatan', 'deskripsi'])
            ->orderBy('nama_bengkel')
            ->get();
        $siswaOptions = Siswa::query()->select(['id', 'nama', 'kelas_id', 'nisn'])
            ->with(['kelas:id,nama_kelas,jurusan_id', 'kelas.jurusan:id,nama_jurusan'])
            ->orderBy('nama')
            ->get()
            ->map(function ($siswa) {
                return [
                    'id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'kelas_id' => $siswa->kelas_id,
                    'nisn' => $siswa->nisn,
                    'kelas_nama' => $siswa->kelas->nama_kelas ?? '-',
                    'jurusan_nama' => $siswa->kelas->jurusan->nama_jurusan ?? '-',
                ];
            });

        return Inertia::render('Admin/Cetak/SuratPengantar/Edit', [
            'suratPengantar' => $suratPengantar,
            'kelasOptions' => $kelasOptions,
            'siswaOptions' => $siswaOptions,
            'bengkelOptions' => $bengkelOptions,
        ]);
    }

    public function update(Request $request, SuratPengantar $suratPengantar)
    {
        $validated = $request->validate([
            'nomor_surat' => 'required|string|max:255',
            'lampiran' => 'nullable|string|max:255',
            'tujuan_surat' => 'required|string|max:255',
            'alamat_tujuan' => 'required|string',
            'tanggal_mulai' => 'required|date_format:Y-m-d',
            'tanggal_selesai' => 'required|date_format:Y-m-d|after_or_equal:tanggal_mulai',
            'tanggal_surat' => 'required|date_format:Y-m-d',
            'kepala_sekolah_nama' => 'required|string|max:255',
            'kepala_sekolah_nbm' => 'nullable|string|max:255',
            'ketua_pkl_nama' => 'required|string|max:255',
            'ketua_pkl_nbm' => 'nullable|string|max:255',
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswas,id',
        ]);

        DB::transaction(function () use ($validated, $suratPengantar) {
            $suratPengantar->update([
                'nomor_surat' => $validated['nomor_surat'],
                'lampiran' => $validated['lampiran'],
                'tujuan_surat' => $validated['tujuan_surat'],
                'alamat_tujuan' => $validated['alamat_tujuan'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'tanggal_surat' => $validated['tanggal_surat'],
                'kepala_sekolah_nama' => $validated['kepala_sekolah_nama'],
                'kepala_sekolah_nbm' => $validated['kepala_sekolah_nbm'],
                'ketua_pkl_nama' => $validated['ketua_pkl_nama'],
                'ketua_pkl_nbm' => $validated['ketua_pkl_nbm'],
            ]);

            $suratPengantar->siswas()->sync($validated['siswa_ids']);
        });

        return redirect()->route('admin.surat.pengantar.index')->with('message', 'Surat Pengantar berhasil diperbarui.');
    }

    public function destroy(SuratPengantar $suratPengantar)
    {
        $suratPengantar->siswas()->detach();
        $suratPengantar->delete();

        return redirect()->back()->with('message', 'Surat Pengantar berhasil dihapus.');
    }

    public function print(SuratPengantar $suratPengantar)
    {
        \Carbon\Carbon::setLocale('id');
        $suratPengantar->load(['siswas.kelas.jurusan']);
        $template = SuratPengantarTemplateSetting::query()->first();

        // Format dates for view
        $tanggalMulai = \Carbon\Carbon::parse($suratPengantar->tanggal_mulai)->translatedFormat('d F');
        $tanggalSelesai = \Carbon\Carbon::parse($suratPengantar->tanggal_selesai)->translatedFormat('d F Y');
        $tanggalSurat = \Carbon\Carbon::parse($suratPengantar->tanggal_surat)->translatedFormat('d F Y');

        // Year logic for "TAHUN 2025/2026" - usually based on current date or academic year
        // Assuming current year / next year
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;
        $tahunAjaran = "$currentYear/$nextYear";

        $defaultPerihal = $template?->default_perihal ?: "Permohonan Tempat Praktik Kerja Lapangan Tahun {$tahunAjaran}";
        $defaultKetuaPklNbm = $template?->ketua_pkl_nbm ?: null;

        $kopSuratFile = $template?->kop_surat_path
            ? Storage::disk('public')->path($template->kop_surat_path)
            : public_path('kopsurat.jpg');

        if (!file_exists($kopSuratFile) && file_exists(public_path('images/kopsurat.jpg'))) {
            $kopSuratFile = public_path('images/kopsurat.jpg');
        }

        $kepalaSekolahTtdFile = $template?->kepala_sekolah_ttd_path
            ? Storage::disk('public')->path($template->kepala_sekolah_ttd_path)
            : public_path('images/ttd1.png');

        $kepalaSekolahStempelFile = $template?->kepala_sekolah_stempel_path
            ? Storage::disk('public')->path($template->kepala_sekolah_stempel_path)
            : public_path('images/stempel1.png');

        $ketuaPklTtdFile = $template?->ketua_pkl_ttd_path
            ? Storage::disk('public')->path($template->ketua_pkl_ttd_path)
            : public_path('images/ttd2.png');

        $ketuaPklStempelFile = $template?->ketua_pkl_stempel_path
            ? Storage::disk('public')->path($template->ketua_pkl_stempel_path)
            : public_path('images/stempel2.png');

        $pdf = Pdf::loadView('pdf.surat_pengantar', compact(
            'suratPengantar',
            'tanggalMulai',
            'tanggalSelesai',
            'tanggalSurat',
            'tahunAjaran',
            'defaultPerihal',
            'defaultKetuaPklNbm',
            'kopSuratFile',
            'kepalaSekolahTtdFile',
            'kepalaSekolahStempelFile',
            'ketuaPklTtdFile',
            'ketuaPklStempelFile'
        ))->setPaper([0, 0, 609.4488, 935.433], 'portrait'); // F4 size in points (215mm x 330mm)

        return $pdf->stream('Surat_Pengantar_'.str_replace('/', '_', $suratPengantar->nomor_surat).'.pdf');
    }
}
