<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bengkel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class BengkelController extends Controller
{
    public function index(Request $request)
    {
        // Auto-sync empty/null kode_bengkel based on current placements (throttled to avoid slow N+1 query loops)
        if ($request->boolean('sync_kode') || Cache::add('bengkel.sync_kode_lock', true, 86400)) {
            $bengkelsToSync = Bengkel::query()
                ->where(function ($q) {
                    $q->whereNull('kode_bengkel')->orWhere('kode_bengkel', '');
                })
                ->whereHas('penempatan_pkl.siswa.kelas.jurusan')
                ->with(['penempatan_pkl.siswa.kelas.jurusan'])
                ->limit(30)
                ->get();

            foreach ($bengkelsToSync as $bengkel) {
                $placement = $bengkel->penempatan_pkl->first();
                if ($placement?->siswa?->kelas?->jurusan) {
                    $majorCode = $placement->siswa->kelas->jurusan->kode_jurusan;
                    if ($majorCode === 'ANIMASI') $majorCode = 'ANS';
                    if ($majorCode === 'KULINER') $majorCode = 'KLR';

                    if (in_array($majorCode, ['TKR', 'TSM', 'TEI', 'ANS', 'KLR'])) {
                        $bengkel->update(['kode_bengkel' => $majorCode]);
                    }
                }
            }
        }

        $query = Bengkel::query();

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('nama_bengkel', 'like', '%'.$request->search.'%');
        }

        // Filter by Dusun
        if ($request->has('dusun') && $request->dusun) {
            $query->where('dusun', 'like', '%'.$request->dusun.'%');
        }

        // Filter by Desa
        if ($request->has('desa') && $request->desa) {
            $query->where('desa', 'like', '%'.$request->desa.'%');
        }

        // Filter by Kecamatan
        if ($request->has('kecamatan') && $request->kecamatan) {
            $query->where('kecamatan', 'like', '%'.$request->kecamatan.'%');
        }

        // Filter by Jurusan (Kode Bengkel)
        if ($request->has('kode_bengkel') && $request->kode_bengkel) {
            $query->where('kode_bengkel', $request->kode_bengkel);
        }

        $perPage = $request->input('per_page', 10); // Default 10
        $validPerPage = [10, 20, 50, 100, 200, 300, 500];

        if (! in_array($perPage, $validPerPage)) {
            $perPage = 10;
        }

        $bengkels = $query->latest()
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'TKR' => Bengkel::query()->where('kode_bengkel', 'TKR')->count('*'),
            'TSM' => Bengkel::query()->where('kode_bengkel', 'TSM')->count('*'),
            'TEI' => Bengkel::query()->where('kode_bengkel', 'TEI')->count('*'),
            'ANS' => Bengkel::query()->where('kode_bengkel', 'ANS')->count('*'),
            'KLR' => Bengkel::query()->where('kode_bengkel', 'KLR')->count('*'),
            'LAIN' => Bengkel::query()->whereNotIn('kode_bengkel', ['TKR', 'TSM', 'TEI', 'ANS', 'KLR'], 'and')
                ->orWhereNull('kode_bengkel')
                ->count('*'),
        ];

        return Inertia::render('Admin/Bengkel/Index', [
            'bengkels' => $bengkels,
            'filters' => $request->only(['search', 'dusun', 'desa', 'kecamatan', 'kode_bengkel', 'per_page']),
            'stats' => $stats,
        ]);
    }

    private function extractCoordinates(?string $link)
    {
        if (empty($link)) {
            return null;
        }

        // Jika short link (goo.gl, maps.app.goo.gl, dll), resolve redirectnya
        if (preg_match('/(goo\.gl|maps\.app\.goo\.gl|bit\.ly)/', $link)) {
            $headers = @get_headers($link, 1);
            if ($headers && isset($headers['Location'])) {
                $link = is_array($headers['Location']) ? end($headers['Location']) : $headers['Location'];
            }
        }

        // Regex 1: Format @lat,lng (URL standar Google Maps)
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $link, $matches)) {
            return [
                'latitude' => $matches[1],
                'longitude' => $matches[2],
            ];
        }

        // Regex 2: Format ?q=lat,lng (URL embed/search)
        if (preg_match('/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/', $link, $matches)) {
            return [
                'latitude' => $matches[1],
                'longitude' => $matches[2],
            ];
        }

        // Regex 3: Format ll=lat,lng (URL embed lama)
        if (preg_match('/[?&]ll=(-?\d+\.\d+),(-?\d+\.\d+)/', $link, $matches)) {
            return [
                'latitude' => $matches[1],
                'longitude' => $matches[2],
            ];
        }

        // Regex 4: Format /place/name/lat,lng (kadang muncul di URL)
        // Tidak seakurat @lat,lng, tapi bisa jadi cadangan

        return null;
    }

    public function store(Request $request)
    {
        // Sanitize coordinates (replace comma with dot and handle empty string)
        $inputs = $request->all();
        if (isset($inputs['latitude'])) {
            $inputs['latitude'] = $inputs['latitude'] === '' ? null : str_replace(',', '.', $inputs['latitude']);
        }
        if (isset($inputs['longitude'])) {
            $inputs['longitude'] = $inputs['longitude'] === '' ? null : str_replace(',', '.', $inputs['longitude']);
        }
        $request->replace($inputs);

        $validated = $request->validate([
            'kode_bengkel' => 'nullable|string|max:50',
            'nama_bengkel' => 'required|string|max:255',
            'nama_pemilik' => 'nullable|string|max:255',
            'dusun' => 'nullable|string|max:255',
            'desa' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'google_maps_link' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'no_hp' => 'nullable|string|max:20',
        ]);

        // Auto-extract coordinates if link provided but coords are empty
        if (! empty($validated['google_maps_link']) && (empty($validated['latitude']) || empty($validated['longitude']))) {
            $coords = $this->extractCoordinates($validated['google_maps_link']);
            if ($coords) {
                $validated['latitude'] = $coords['latitude'];
                $validated['longitude'] = $coords['longitude'];
            }
        }

        Bengkel::create($validated);
        Cache::forget('admin_dashboard_stats');
        Cache::forget('landing.map_data');

        return redirect()->back()->with('message', 'Data bengkel berhasil ditambahkan.');
    }

    public function update(Request $request, int|string $id)
    {
        $bengkel = Bengkel::findOrFail($id);

        // Sanitize coordinates (replace comma with dot and handle empty string)
        $inputs = $request->all();
        if (isset($inputs['latitude'])) {
            $inputs['latitude'] = $inputs['latitude'] === '' ? null : str_replace(',', '.', $inputs['latitude']);
        }
        if (isset($inputs['longitude'])) {
            $inputs['longitude'] = $inputs['longitude'] === '' ? null : str_replace(',', '.', $inputs['longitude']);
        }
        $request->replace($inputs);

        $validated = $request->validate([
            'kode_bengkel' => 'nullable|string|max:50',
            'nama_bengkel' => 'required|string|max:255',
            'nama_pemilik' => 'nullable|string|max:255',
            'dusun' => 'nullable|string|max:255',
            'desa' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'google_maps_link' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'no_hp' => 'nullable|string|max:20',
        ]);

        // Auto-extract coordinates if link provided but coords are empty
        if (! empty($validated['google_maps_link']) && (empty($validated['latitude']) || empty($validated['longitude']))) {
            $coords = $this->extractCoordinates($validated['google_maps_link']);
            if ($coords) {
                $validated['latitude'] = $coords['latitude'];
                $validated['longitude'] = $coords['longitude'];
            }
        }

        $bengkel->update($validated);
        Cache::forget('admin_dashboard_stats');
        Cache::forget('landing.map_data');

        return redirect()->back()->with('message', 'Data bengkel berhasil diperbarui.');
    }

    public function destroy(int|string $id)
    {
        $bengkel = Bengkel::findOrFail($id);
        $bengkel->delete();
        Cache::forget('admin_dashboard_stats');
        Cache::forget('landing.map_data');

        return redirect()->back()->with('message', 'Data bengkel berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:bengkels,id',
        ]);

        Bengkel::query()->whereIn('id', $request->ids)->delete();
        Cache::forget('admin_dashboard_stats');
        Cache::forget('landing.map_data');

        return redirect()->back()->with('message', 'Data bengkel berhasil dihapus masal.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $file = $request->file('file');
        $extension = strtolower((string) ($file?->getClientOriginalExtension() ?: $file?->extension()));
        if ($extension === 'xlsx' && ! class_exists(\ZipArchive::class)) {
            return redirect()->back()->with('error', 'Import gagal: server belum mengaktifkan ekstensi ZIP (ZipArchive). Aktifkan ext-zip atau gunakan file CSV.');
        }

        try {
            @ini_set('memory_limit', '512M');
            @set_time_limit(0);

            $importer = new \App\Imports\BengkelImport;
            Excel::import($importer, $file);
            Cache::forget('admin_dashboard_stats');

            $summary = $importer->summary();
            $successfulCount = (int) ($summary['inserted'] ?? 0) + (int) ($summary['updated'] ?? 0);
            $message = ($successfulCount > 0 ? 'Berhasil import data bengkel. ' : 'Import selesai. ')
                .'Ditambah: '.$summary['inserted'].', Diperbarui: '.$summary['updated'].', Dilewati: '.$summary['skipped'].'.';

            if (! empty($summary['errors'])) {
                $errorText = 'Beberapa baris dilewati: '.implode(' | ', array_slice($summary['errors'], 0, 5));

                return redirect()->back()->with('message', $message)->with('error', $errorText);
            }

            if ($successfulCount <= 0) {
                return redirect()->back()->with('error', 'Import selesai tapi tidak ada data yang masuk ke database. Pastikan format kolom sesuai template dan kolom "nama_bengkel" terisi.');
            }

            return redirect()->back()->with('message', $message);
        } catch (\Throwable $e) {
            Log::error('Import bengkel gagal', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            $message = $e->getMessage();
            if (stripos($message, 'ZipArchive') !== false || stripos($message, 'zip') !== false) {
                $message = 'Server belum mengaktifkan ekstensi ZIP (ZipArchive) untuk membaca file .xlsx. Aktifkan ext-zip atau gunakan file CSV.';
            } elseif (stripos($message, 'Maximum execution time') !== false) {
                $message = 'Proses import melebihi batas waktu (timeout). Kurangi jumlah baris per import atau naikkan max_execution_time di hosting.';
            } elseif (stripos($message, 'Allowed memory size') !== false) {
                $message = 'Proses import kehabisan memori. Kurangi jumlah baris per import atau naikkan memory_limit di hosting.';
            }

            return redirect()->back()->with('error', 'Import gagal: '.$message);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new \App\Exports\BengkelTemplateExport, 'template_bengkel.xlsx');
    }

    public function export()
    {
        return Excel::download(new \App\Exports\BengkelExport, 'data_bengkel.xlsx');
    }
}
