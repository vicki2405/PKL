<?php

namespace App\Http\Middleware;

use App\Models\Monitoring;
use App\Models\MutasiPKL;
use App\Models\User;
use App\Support\AuthorizationCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        if (file_exists($manifest = public_path('build/manifest.json'))) {
            return Cache::remember('vite_manifest_md5', 60, fn () => md5_file($manifest));
        }

        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    private ?array $sharedRoles = null;
    private ?array $sharedPermissions = null;

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => fn () => $this->buildSharedAuth($request),
            'flash' => [
                'message' => $request->session()->get('message'),
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'notifications' => fn () => $this->buildNotifications($request),
        ];
    }

    private function getNotificationsCached(): array
    {
        return [
            'mutasi_pending_count' => Cache::remember('mutasi_pending_count', 300, function () {
                return MutasiPKL::query()->where('status', 'pending')->count();
            }),
            'pelanggaran_pending_count' => Cache::remember('pelanggaran_pending_count', 300, function () {
                return Monitoring::query()
                    ->where('laporan_diterima', false)
                    ->whereHas('details', fn ($q) => $q->where('is_pelanggaran', true))
                    ->count();
            }),
        ];
    }

    private function buildNotifications(Request $request): array
    {
        $user = $request->user();
        if (! $user) {
            return ['mutasi_pending_count' => 0, 'pelanggaran_pending_count' => 0];
        }

        $roles = $this->getRoles($user);
        $permissions = $this->getAllPermissions($user);

        $canSeeMutasi = in_array('admin', $roles, true)
            || in_array('super-admin', $roles, true)
            || in_array('mutasi_pkl.view', $permissions, true);

        $canSeeMonitoring = in_array('admin', $roles, true)
            || in_array('super-admin', $roles, true)
            || in_array('monitoring.view', $permissions, true);

        if (! $canSeeMutasi && ! $canSeeMonitoring) {
            return ['mutasi_pending_count' => 0, 'pelanggaran_pending_count' => 0];
        }

        $cached = $this->getNotificationsCached();
        return [
            'mutasi_pending_count' => $canSeeMutasi ? $cached['mutasi_pending_count'] : 0,
            'pelanggaran_pending_count' => $canSeeMonitoring ? $cached['pelanggaran_pending_count'] : 0,
        ];
    }

    private function getRoles(User $user): array
    {
        if ($this->sharedRoles === null) {
            $this->sharedRoles = Cache::remember(AuthorizationCache::rolesKey($user->id), 3600, function () use ($user) {
                return method_exists($user, 'getRoleNames') ? $user->getRoleNames()->toArray() : [];
            });
        }
        return $this->sharedRoles;
    }

    private function getAllPermissions(User $user): array
    {
        if ($this->sharedPermissions === null) {
            $this->sharedPermissions = Cache::remember(AuthorizationCache::permissionsKey($user->id), 3600, function () use ($user) {
                return method_exists($user, 'getAllPermissions')
                    ? $user->getAllPermissions()->pluck('name')->toArray()
                    : [];
            });
            $this->sharedPermissions = is_array($this->sharedPermissions) ? $this->sharedPermissions : [];
        }
        return $this->sharedPermissions;
    }

    private function buildSharedAuth(Request $request): array
    {
        $user = $request->user();

        if (! $user) {
            return ['user' => null];
        }

        $roles = $this->getRoles($user);
        $menuPermissionList = $this->getMenuPermissionList();
        $hasAnyPermission = false;

        if (in_array('admin', $roles, true) || in_array('super-admin', $roles, true)) {
            $allPermissions = $this->getAllPermissions($user);
            $permissions = array_values(array_unique(array_merge($menuPermissionList, $allPermissions)));
            $hasAnyPermission = true;
        } else {
            $permissions = $this->getAllPermissions($user);
            $hasAnyPermission = count(array_intersect($permissions, $menuPermissionList)) > 0;
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'roles' => $roles,
                'permissions' => $permissions,
                'has_any_permission' => (bool) $hasAnyPermission,
                'profile_photo_url' => $this->resolveProfilePhotoUrl($user, $roles),
            ],
        ];
    }

    private function getMenuPermissionList(): array
    {
        return [
            'view dashboard',
            'jurusan.view',
            'kelas.view',
            'siswa.view',
            'jabatan_pembimbing.view',
            'pembimbing.view',
            'bengkel.view',
            'input_data_pkl.view',
            'input_pembimbing.view',
            'mutasi_pkl.view',
            'monitoring.view',
            'rekap_absensi.view',
            'rekap_absensi.export',
            'rekap_absensi_pembekalan.view',
            'rekap_absensi_pembekalan.export',
            'pembekalan_aktivasi.view',
            'cetak_p1.view',
            'surat_pengantar.view',
            'rekap_data_pkl.view',
            'rekap_karir.view',
            'informasi.view',
            'rekap_problem.view',
            'rekap_problem.export_excel',
            'rekap_problem.export_pdf',
            'user_pembimbing.view',
            'user_siswa.view',
            'data_admin.view',
            'role.view',
            'permission.view',
            'backup.view',
        ];
    }

    private function resolveProfilePhotoUrl(User $user, array $roles): ?string
    {
        return Cache::remember(AuthorizationCache::profilePhotoKey($user->id), 3600, function () use ($user, $roles) {
            if (in_array('siswa', $roles, true) && method_exists($user, 'siswa')) {
                $user->loadMissing('siswa');
                $siswa = $user->siswa;
                if ($siswa) {
                    $siswa->loadMissing('media');
                    $media = $siswa->getFirstMedia('profile_photos');
                    if ($media) {
                        $path = $media->getPath();
                        if ($path && is_file($path)) {
                            return route('siswa.profile.photo', ['v' => $media->updated_at?->timestamp]);
                        }
                    }
                    return null;
                }
            }

            if (in_array('pembimbing', $roles, true) && method_exists($user, 'pembimbing')) {
                $user->loadMissing('pembimbing');
                $pembimbing = $user->pembimbing;
                if ($pembimbing && method_exists($pembimbing, 'getFirstMedia')) {
                    $media = $pembimbing->getFirstMedia('profile_photos');
                    if ($media && $media->getPath() && is_file($media->getPath())) {
                        return $media->getUrl();
                    }
                }
                return null;
            }

            return null;
        });
    }
}
