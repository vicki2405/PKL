<?php

use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BengkelController;
use App\Http\Controllers\Admin\CetakController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InformasiController;
use App\Http\Controllers\Admin\InputDataPKLController;
use App\Http\Controllers\Admin\JabatanPembimbingController;
use App\Http\Controllers\Admin\JurusanController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\MonitoringPenempatanController;
use App\Http\Controllers\Admin\MutasiPKLController;
use App\Http\Controllers\Admin\PembimbingController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RekapAbsensiController;
use App\Http\Controllers\Admin\RekapDataPKLController;
use App\Http\Controllers\Admin\RekapKarirController;
use App\Http\Controllers\Admin\RekapProblemController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\SuratPengantarController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserPembimbingController;
use App\Http\Controllers\Admin\UserSiswaController;
use App\Models\User;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/storage/{path}', function (string $path) {
    $path = ltrim($path, '/');
    if ($path === '' || str_contains($path, '..') || preg_match('/\.(env|git|php|htaccess|sh|sql|json|lock|key)$/i', $path)) {
        abort(404);
    }

    $disk = Storage::disk('public');
    if (! $disk->exists($path)) {
        abort(404);
    }

    return response()->file($disk->path($path), [
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('path', '.*');

Route::get('/', function () {
    $mapData = \Illuminate\Support\Facades\Cache::remember('landing.map_data', 300, function () {
        return \App\Models\Bengkel::select(['id', 'nama_bengkel', 'latitude', 'longitude', 'google_maps_link'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->withCount('penempatan_pkl')
            ->get()
            ->map(function ($bengkel) {
                return [
                    'id' => $bengkel->id,
                    'name' => $bengkel->nama_bengkel,
                    'lat' => (float) $bengkel->latitude,
                    'lng' => (float) $bengkel->longitude,
                    'students_count' => $bengkel->penempatan_pkl_count,
                    'link' => $bengkel->google_maps_link,
                ];
            });
    });

    $tableData = \Illuminate\Support\Facades\Cache::remember('landing.table_data', 300, function () {
        return \App\Models\Bengkel::withCount('penempatan_pkl')
            ->orderBy('nama_bengkel')
            ->get()
            ->map(function ($bengkel) {
                return [
                    'id' => $bengkel->id,
                    'nama_bengkel' => $bengkel->nama_bengkel,
                    'kode_bengkel' => $bengkel->kode_bengkel ?: '-',
                    'kecamatan' => $bengkel->kecamatan ?: '-',
                    'google_maps_link' => $bengkel->google_maps_link,
                    'jumlah_peserta' => $bengkel->penempatan_pkl_count,
                ];
            });
    });

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => false,
        'mapData' => $mapData,
        'tableData' => $tableData,
    ]);
});

Route::get('/dashboard', function () {
    /** @var User|null $user */
    $user = Auth::user();

    if (! $user) {
        return abort(403, 'Unauthorized action.');
    }

    if ($user->hasAnyRole(['admin', 'super-admin', 'tamu', 'BK', 'bendahara'])) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('siswa')) {
        return redirect()->route('siswa.dashboard');
    }

    if ($user->hasRole('pembimbing')) {
        return redirect()->route('pembimbing.dashboard');
    }

    return abort(403, 'Unauthorized action.');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:siswa'])->prefix('siswa')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Siswa\DashboardController::class, 'index'])->name('siswa.dashboard');

    Route::get('/profile', [\App\Http\Controllers\Siswa\ProfileController::class, 'edit'])->name('siswa.profile');
    Route::get('/profile-photo', [\App\Http\Controllers\Siswa\ProfileController::class, 'photo'])->name('siswa.profile.photo');
    Route::post('/profile', [\App\Http\Controllers\Siswa\ProfileController::class, 'update'])->name('siswa.profile.update');

    Route::get('/mutasi-pkl/create', [\App\Http\Controllers\Siswa\MutasiPKLController::class, 'create'])->name('siswa.mutasi_pkl.create');
    Route::post('/mutasi-pkl', [\App\Http\Controllers\Siswa\MutasiPKLController::class, 'store'])->name('siswa.mutasi_pkl.store');

    Route::get('/karir', [\App\Http\Controllers\Siswa\KarirController::class, 'edit'])->name('siswa.karir.edit');
    Route::post('/karir', [\App\Http\Controllers\Siswa\KarirController::class, 'update'])->name('siswa.karir.update');
});

Route::middleware(['auth', 'verified', 'role:pembimbing'])->prefix('pembimbing')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Pembimbing\DashboardController::class, 'index'])->name('pembimbing.dashboard');
    Route::resource('monitoring', \App\Http\Controllers\Pembimbing\MonitoringController::class)->except(['show'])->names([
        'index' => 'pembimbing.monitoring.index',
        'create' => 'pembimbing.monitoring.create',
        'store' => 'pembimbing.monitoring.store',
        'edit' => 'pembimbing.monitoring.edit',
        'update' => 'pembimbing.monitoring.update',
        'destroy' => 'pembimbing.monitoring.destroy',
    ]);
    Route::get('/informasi', [\App\Http\Controllers\Pembimbing\InformasiController::class, 'index'])->name('pembimbing.informasi.index');
    Route::get('/absensi-pembekalan', [\App\Http\Controllers\Pembimbing\AbsensiPembekalanController::class, 'index'])->name('pembimbing.absensi-pembekalan.index');
    Route::post('/absensi-pembekalan', [\App\Http\Controllers\Pembimbing\AbsensiPembekalanController::class, 'store'])->name('pembimbing.absensi-pembekalan.store');
});

Route::middleware(['auth', 'verified', 'role:admin|super-admin|tamu|BK|bendahara', 'admin.write'])->prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::resource('jurusan', JurusanController::class)->names([
        'index' => 'admin.jurusan.index',
        'create' => 'admin.jurusan.create',
        'store' => 'admin.jurusan.store',
        'show' => 'admin.jurusan.show',
        'edit' => 'admin.jurusan.edit',
        'update' => 'admin.jurusan.update',
        'destroy' => 'admin.jurusan.destroy',
    ]);
    Route::resource('kelas', KelasController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->names([
        'index' => 'admin.kelas.index',
        'create' => 'admin.kelas.create',
        'store' => 'admin.kelas.store',
        'edit' => 'admin.kelas.edit',
        'update' => 'admin.kelas.update',
        'destroy' => 'admin.kelas.destroy',
    ]);
    Route::get('siswa', [SiswaController::class, 'index'])->name('admin.siswa.index');
    Route::middleware('role:admin|super-admin')->group(function () {
        Route::get('siswa/create', [SiswaController::class, 'create'])->name('admin.siswa.create');
        Route::post('siswa', [SiswaController::class, 'store'])->name('admin.siswa.store');
        Route::get('siswa/{siswa}/edit', [SiswaController::class, 'edit'])->name('admin.siswa.edit');
        Route::put('siswa/{siswa}', [SiswaController::class, 'update'])->name('admin.siswa.update');
        Route::delete('siswa/{siswa}', [SiswaController::class, 'destroy'])->name('admin.siswa.destroy');

        Route::get('siswa/export', [SiswaController::class, 'export'])->name('admin.siswa.export');
        Route::post('siswa/import', [SiswaController::class, 'import'])->name('admin.siswa.import');
        Route::get('siswa/template', [SiswaController::class, 'downloadTemplate'])->name('admin.siswa.template');
        Route::post('siswa/bulk-destroy', [SiswaController::class, 'bulkDestroy'])->name('admin.siswa.bulkDestroy');
    });

    Route::get('/forms', function () {
        return Inertia::render('Admin/Forms');
    })->name('admin.forms');

    Route::get('/ui', function () {
        return Inertia::render('Admin/Ui');
    })->name('admin.ui');

    Route::get('/responsive', function () {
        return Inertia::render('Admin/Responsive');
    })->name('admin.responsive');

    Route::get('/styles', function () {
        return Inertia::render('Admin/Styles');
    })->name('admin.styles');

    Route::resource('jabatan-pembimbing', JabatanPembimbingController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names([
            'index' => 'admin.jabatan_pembimbing.index',
            'store' => 'admin.jabatan_pembimbing.store',
            'update' => 'admin.jabatan_pembimbing.update',
            'destroy' => 'admin.jabatan_pembimbing.destroy',
        ]);

    Route::resource('pembimbing', PembimbingController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names([
            'index' => 'admin.pembimbing.index',
            'store' => 'admin.pembimbing.store',
            'update' => 'admin.pembimbing.update',
            'destroy' => 'admin.pembimbing.destroy',
        ]);

    Route::get('/bengkel', [BengkelController::class, 'index'])->name('admin.bengkel.index');
    Route::post('/bengkel/bulk-destroy', [BengkelController::class, 'bulkDestroy'])->name('admin.bengkel.bulk_destroy');
    Route::post('/bengkel', [BengkelController::class, 'store'])->name('admin.bengkel.store');
    Route::put('/bengkel/{bengkel}', [BengkelController::class, 'update'])->name('admin.bengkel.update');
    Route::delete('/bengkel/{bengkel}', [BengkelController::class, 'destroy'])->name('admin.bengkel.destroy');
    Route::post('/bengkel/import', [BengkelController::class, 'import'])->name('admin.bengkel.import');
    Route::get('/bengkel/export', [BengkelController::class, 'export'])->name('admin.bengkel.export');
    Route::get('/bengkel/template', [BengkelController::class, 'downloadTemplate'])->name('admin.bengkel.template');

    Route::get('/input-data-pkl', [InputDataPKLController::class, 'index'])->name('admin.input_data_pkl.index');
    Route::get('/input-data-pkl/input-pembimbing', [InputDataPKLController::class, 'inputPembimbingIndex'])
        ->middleware('role:admin|super-admin')
        ->name('admin.input_data_pkl.input_pembimbing');
    Route::get('/input-data-pkl/input-pembimbing/new', [InputDataPKLController::class, 'inputPembimbingForm'])
        ->middleware('role:admin|super-admin')
        ->name('admin.input_data_pkl.input_pembimbing.form');
    Route::get('/input-data-pkl/input-pembimbing/export-excel', [InputDataPKLController::class, 'inputPembimbingExportExcel'])
        ->middleware('role:admin|super-admin')
        ->name('admin.input_data_pkl.input_pembimbing.export_excel');
    Route::get('/input-data-pkl/input-pembimbing/export-pdf', [InputDataPKLController::class, 'inputPembimbingExportPdf'])
        ->middleware('role:admin|super-admin')
        ->name('admin.input_data_pkl.input_pembimbing.export_pdf');
    Route::get('/input-data-pkl/input-pembimbing/{pembimbing}', [InputDataPKLController::class, 'inputPembimbingShow'])
        ->middleware('role:admin|super-admin')
        ->name('admin.input_data_pkl.input_pembimbing.show');
    Route::delete('/input-data-pkl/input-pembimbing/{pembimbing}/bengkel/{bengkel}', [InputDataPKLController::class, 'inputPembimbingDetachBengkel'])
        ->middleware('role:admin|super-admin')
        ->name('admin.input_data_pkl.input_pembimbing.detach_bengkel');
    Route::get('/input-data-pkl/create', [InputDataPKLController::class, 'create'])->name('admin.input_data_pkl.create');
    Route::get('/input-data-pkl/siswa-options', [InputDataPKLController::class, 'siswaOptions'])->name('admin.input_data_pkl.siswa_options');
    Route::post('/input-data-pkl', [InputDataPKLController::class, 'store'])->name('admin.input_data_pkl.store');
    Route::put('/input-data-pkl/{bengkel}/set-pembimbing', [InputDataPKLController::class, 'setPembimbing'])->name('admin.input_data_pkl.set_pembimbing');
    Route::post('/input-data-pkl/bulk-set-pembimbing', [InputDataPKLController::class, 'bulkSetPembimbing'])
        ->middleware('role:admin|super-admin')
        ->name('admin.input_data_pkl.bulk_set_pembimbing');
    Route::post('/input-data-pkl/import', [InputDataPKLController::class, 'import'])->name('admin.input_data_pkl.import');
    Route::get('/input-data-pkl/template', [InputDataPKLController::class, 'downloadTemplate'])->name('admin.input_data_pkl.template');
    Route::get('/input-data-pkl/export-excel', [InputDataPKLController::class, 'exportExcel'])->name('admin.input_data_pkl.export_excel');
    Route::post('/input-data-pkl/bulk-destroy', [InputDataPKLController::class, 'bulkDestroy'])->name('admin.input_data_pkl.bulk_destroy');
    Route::get('/input-data-pkl/{bengkel}', [InputDataPKLController::class, 'show'])->name('admin.input_data_pkl.show');
    Route::get('/input-data-pkl/{bengkel}/edit', [InputDataPKLController::class, 'edit'])->name('admin.input_data_pkl.edit');
    Route::put('/input-data-pkl/{bengkel}', [InputDataPKLController::class, 'update'])->name('admin.input_data_pkl.update');
    Route::delete('/input-data-pkl/{bengkel}', [InputDataPKLController::class, 'destroy'])->name('admin.input_data_pkl.destroy');

    Route::get('/mutasi-pkl/export', [MutasiPKLController::class, 'export'])->name('admin.mutasi_pkl.export');
    Route::get('/mutasi-pkl', [MutasiPKLController::class, 'index'])->name('admin.mutasi_pkl.index');
    Route::get('/mutasi-pkl/create', [MutasiPKLController::class, 'create'])->name('admin.mutasi_pkl.create');
    Route::get('/mutasi-pkl/siswa-options', [MutasiPKLController::class, 'siswaOptions'])->name('admin.mutasi_pkl.siswa_options');
    Route::post('/mutasi-pkl', [MutasiPKLController::class, 'store'])->name('admin.mutasi_pkl.store');
    Route::put('/mutasi-pkl/{mutasi}', [MutasiPKLController::class, 'update'])->name('admin.mutasi_pkl.update');
    Route::post('/mutasi-pkl/{mutasi}/process', [MutasiPKLController::class, 'process'])->name('admin.mutasi_pkl.process');
    Route::get('/mutasi-pkl/{mutasi}/review', [MutasiPKLController::class, 'review'])->name('admin.mutasi_pkl.review');
    Route::put('/mutasi-pkl/{mutasi}/approve', [MutasiPKLController::class, 'approve'])->name('admin.mutasi_pkl.approve');
    Route::put('/mutasi-pkl/{mutasi}/reject', [MutasiPKLController::class, 'reject'])->name('admin.mutasi_pkl.reject');
    Route::delete('/mutasi-pkl/{mutasi}', [MutasiPKLController::class, 'destroy'])->name('admin.mutasi_pkl.destroy');
    Route::get('/monitoring-penempatan', [MonitoringPenempatanController::class, 'index'])->name('admin.monitoring_penempatan.index');

    Route::get('/monitoring/export-excel', [MonitoringController::class, 'exportExcel'])->name('admin.monitoring.export-excel');
    Route::get('/monitoring/export-pdf', [MonitoringController::class, 'exportPdf'])->name('admin.monitoring.export-pdf');
    Route::get('/monitoring/setting', [MonitoringController::class, 'setting'])->name('admin.monitoring.setting');
    Route::post('/monitoring/period-setting', [MonitoringController::class, 'savePeriodSetting'])->name('admin.monitoring.period-setting.save');
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('admin.monitoring.index');
    Route::get('/monitoring/{monitoring}', [MonitoringController::class, 'show'])->name('admin.monitoring.show');
    Route::put('/monitoring/{monitoring}/update-details', [MonitoringController::class, 'updateDetails'])->name('admin.monitoring.update-details');
    Route::put('/monitoring/terima-semua-pelanggaran', [MonitoringController::class, 'terimaSemuaPelanggaran'])->name('admin.monitoring.terima-semua-pelanggaran');
    Route::put('/monitoring/{monitoring}/terima', [MonitoringController::class, 'terimaLaporan'])->name('admin.monitoring.terima');
    Route::delete('/monitoring/{monitoring}', [MonitoringController::class, 'destroy'])
        ->middleware('role:admin|super-admin')
        ->name('admin.monitoring.destroy');

    Route::get('/rekap-absensi/export-excel', [RekapAbsensiController::class, 'exportExcel'])->name('admin.rekap-absensi.export-excel');
    Route::get('/rekap-absensi/export-pdf', [RekapAbsensiController::class, 'exportPdf'])->name('admin.rekap-absensi.export-pdf');
    Route::get('/rekap-absensi/pembekalan/export-excel', [RekapAbsensiController::class, 'exportPembekalanExcel'])->name('admin.rekap-absensi.pembekalan.export-excel');
    Route::get('/rekap-absensi/pembekalan/export-pdf', [RekapAbsensiController::class, 'exportPembekalanPdf'])->name('admin.rekap-absensi.pembekalan.export-pdf');
    Route::get('/rekap-absensi/pembekalan/create', [RekapAbsensiController::class, 'pembekalanCreate'])->name('admin.rekap-absensi.pembekalan.create');
    Route::post('/rekap-absensi/pembekalan', [RekapAbsensiController::class, 'pembekalanStore'])->name('admin.rekap-absensi.pembekalan.store');
    Route::get('/rekap-absensi/pembekalan/cetak', [RekapAbsensiController::class, 'pembekalanCetak'])->name('admin.rekap-absensi.pembekalan.cetak');
    Route::get('/rekap-absensi', [RekapAbsensiController::class, 'index'])->name('admin.rekap-absensi.index');

    Route::get('/pembekalan-aktivasi', [RekapAbsensiController::class, 'aktivasiIndex'])->name('admin.pembekalan-aktivasi.index');
    Route::post('/pembekalan-aktivasi', [RekapAbsensiController::class, 'aktivasiStore'])->name('admin.pembekalan-aktivasi.store');
    Route::put('/pembekalan-aktivasi/{aktivasi}/toggle', [RekapAbsensiController::class, 'aktivasiToggle'])->name('admin.pembekalan-aktivasi.toggle');
    Route::delete('/pembekalan-aktivasi/{aktivasi}', [RekapAbsensiController::class, 'aktivasiDestroy'])->name('admin.pembekalan-aktivasi.destroy');

    Route::get('/rekap-data-pkl', [RekapDataPKLController::class, 'index'])->name('admin.rekap_data_pkl.index');
    Route::get('/rekap-data-pkl/{pembimbing}/detail', [RekapDataPKLController::class, 'detail'])->name('admin.rekap_data_pkl.detail');
    Route::get('/rekap-data-pkl/export-excel', [RekapDataPKLController::class, 'exportExcel'])->name('admin.rekap_data_pkl.export_excel');
    Route::get('/rekap-data-pkl/export-pdf', [RekapDataPKLController::class, 'exportPdf'])->name('admin.rekap_data_pkl.export_pdf');

    Route::get('/rekap-karir', [RekapKarirController::class, 'index'])->name('admin.rekap_karir.index');
    Route::post('/rekap-karir/toggle-status', [RekapKarirController::class, 'toggleStatus'])->name('admin.rekap_karir.toggle_status');
    Route::put('/rekap-karir/{siswa}', [RekapKarirController::class, 'update'])->name('admin.rekap_karir.update');
    Route::get('/rekap-karir/export-excel', [RekapKarirController::class, 'exportExcel'])->name('admin.rekap_karir.export_excel');
    Route::get('/rekap-karir/export-pdf', [RekapKarirController::class, 'exportPdf'])->name('admin.rekap_karir.export_pdf');

    Route::get('/surat-pengantar', [SuratPengantarController::class, 'index'])->name('admin.surat.pengantar.index');
    Route::get('/surat-pengantar/create', [SuratPengantarController::class, 'create'])->name('admin.surat.pengantar.create');
    Route::post('/surat-pengantar', [SuratPengantarController::class, 'store'])->name('admin.surat.pengantar.store');
    Route::post('/surat-pengantar/template-settings', [SuratPengantarController::class, 'saveTemplateSettings'])->name('admin.surat.pengantar.template-settings.save');
    Route::get('/surat-pengantar/{suratPengantar}/edit', [SuratPengantarController::class, 'edit'])->name('admin.surat.pengantar.edit');
    Route::put('/surat-pengantar/{suratPengantar}', [SuratPengantarController::class, 'update'])->name('admin.surat.pengantar.update');
    Route::delete('/surat-pengantar/{suratPengantar}', [SuratPengantarController::class, 'destroy'])->name('admin.surat.pengantar.destroy');
    Route::get('/surat-pengantar/{suratPengantar}/print', [SuratPengantarController::class, 'print'])->name('admin.surat.pengantar.print');

    Route::get('/cetak-p1', [CetakController::class, 'indexP1'])->name('admin.cetak.p1');
    Route::get('/cetak-p1/{id}/print', [CetakController::class, 'printP1'])->name('admin.cetak.p1.print');
    Route::delete('/cetak-p1/{mutasi}', [CetakController::class, 'destroyP1'])
        ->middleware(['role:admin|super-admin', 'permission:cetak_p1.delete'])
        ->name('admin.cetak.p1.destroy');

    Route::resource('informasi', InformasiController::class)->names('admin.informasi');

    Route::get('/rekap-problem/export', [RekapProblemController::class, 'export'])->name('admin.rekap_problem.export');
    Route::get('/rekap-problem/siswa-options', [RekapProblemController::class, 'siswaOptions'])->name('admin.rekap_problem.siswa_options');
    Route::resource('rekap-problem', RekapProblemController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->names([
            'index' => 'admin.rekap_problem.index',
            'create' => 'admin.rekap_problem.create',
            'store' => 'admin.rekap_problem.store',
            'edit' => 'admin.rekap_problem.edit',
            'update' => 'admin.rekap_problem.update',
            'destroy' => 'admin.rekap_problem.destroy',
        ]);

    Route::resource('data-admin', UserController::class)->only(['index', 'store', 'update', 'destroy'])->names([
        'index' => 'admin.users.index',
        'store' => 'admin.users.store',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);

    Route::resource('roles', RoleController::class)->names([
        'index' => 'admin.roles.index',
        'store' => 'admin.roles.store',
        'update' => 'admin.roles.update',
        'destroy' => 'admin.roles.destroy',
    ]);

    Route::resource('permissions', PermissionController::class)->only(['index', 'store', 'update', 'destroy'])->names([
        'index' => 'admin.permissions.index',
        'store' => 'admin.permissions.store',
        'update' => 'admin.permissions.update',
        'destroy' => 'admin.permissions.destroy',
    ]);

    Route::get('/backup', [BackupController::class, 'index'])->name('admin.backup.index');
    Route::get('/backup/export', [BackupController::class, 'export'])->name('admin.backup.export');
    Route::post('/backup/import', [BackupController::class, 'import'])->name('admin.backup.import');

    if (app()->environment('local')) {
        Route::get('/debug-pembimbing', function () {
            $total = \Illuminate\Support\Facades\DB::table('pembimbing')->count();
            $unlinked = \Illuminate\Support\Facades\DB::table('pembimbing')->whereNull('user_id')->count();
            $linked = \Illuminate\Support\Facades\DB::table('pembimbing')->whereNotNull('user_id')->count();
            $sample = \App\Models\Pembimbing::query()->limit(5)->get();

            return response()->json([
                'total' => $total,
                'unlinked' => $unlinked,
                'linked' => $linked,
                'sample' => $sample,
            ]);
        });
    }

    Route::resource('users/pembimbing', UserPembimbingController::class)->only(['index', 'store', 'update', 'destroy'])->names([
        'index' => 'admin.users.pembimbing.index',
        'store' => 'admin.users.pembimbing.store',
        'update' => 'admin.users.pembimbing.update',
        'destroy' => 'admin.users.pembimbing.destroy',
    ]);
    Route::post('users/pembimbing/import', [UserPembimbingController::class, 'import'])->name('admin.users.pembimbing.import');
    Route::get('users/pembimbing/template', [UserPembimbingController::class, 'downloadTemplate'])->name('admin.users.pembimbing.template');
    Route::post('users/pembimbing/bulk-destroy', [UserPembimbingController::class, 'bulkDestroy'])->name('admin.users.pembimbing.bulk_destroy');
    Route::post('users/pembimbing/bulk-reset-password', [UserPembimbingController::class, 'bulkResetPassword'])->name('admin.users.pembimbing.bulk_reset_password');
    Route::put('users/pembimbing/{id}/reset-password', [UserPembimbingController::class, 'resetPassword'])->name('admin.users.pembimbing.reset_password');

    Route::post('users/siswa/bulk-destroy', [UserSiswaController::class, 'bulkDestroy'])->name('admin.users.siswa.bulk_destroy');
    Route::post('users/siswa/bulk-reset-password', [UserSiswaController::class, 'bulkResetPassword'])->name('admin.users.siswa.bulk_reset_password');
    Route::put('users/siswa/{id}/reset-password', [UserSiswaController::class, 'resetPassword'])->name('admin.users.siswa.reset_password');
    Route::get('users/siswa/candidates', [UserSiswaController::class, 'candidates'])->name('admin.users.siswa.candidates');
    Route::resource('users/siswa', UserSiswaController::class)->only(['index', 'store', 'update', 'destroy'])->names([
        'index' => 'admin.users.siswa.index',
        'store' => 'admin.users.siswa.store',
        'update' => 'admin.users.siswa.update',
        'destroy' => 'admin.users.siswa.destroy',
    ]);
    Route::post('users/siswa/import', [UserSiswaController::class, 'import'])->name('admin.users.siswa.import');
    Route::get('users/siswa/template', [UserSiswaController::class, 'downloadTemplate'])->name('admin.users.siswa.template');

    Route::get('/profile', function () {
        return Inertia::render('Admin/Profile');
    })->name('admin.profile');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

if (app()->environment('local')) {
    Route::get('/seed-coordinates', function () {
        $bengkels = \Illuminate\Support\Facades\DB::table('bengkels')->whereNull('latitude')->orWhereNull('longitude')->get();
        $count = 0;
        foreach ($bengkels as $bengkel) {
            // Random coords around Sidoarjo/Surabaya (-7.4, 112.7)
            $lat = -7.4 + (mt_rand(-100, 100) / 1000);
            $lng = 112.7 + (mt_rand(-100, 100) / 1000);

            \Illuminate\Support\Facades\DB::table('bengkels')->where('id', $bengkel->id)->update([
                'latitude' => $lat,
                'longitude' => $lng,
            ]);
            $count++;
        }

        return "Updated $count bengkels with dummy coordinates. <a href='/'>Go to Landing Page</a>";
    });
}

require __DIR__.'/auth.php';
