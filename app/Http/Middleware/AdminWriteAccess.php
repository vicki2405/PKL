<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\AuthorizationCache;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminWriteAccess
{
    private static array $permissionMap = [];
    private static array $roleCache = [];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $roles = $this->getCachedRoles($user);
        if (in_array('admin', $roles, true) || in_array('super-admin', $roles, true)) {
            return $next($request);
        }

        $route = $request->route();
        $name = $route ? ($route->getName() ?? '') : '';

        $allowedReadModules = ['monitoring', 'rekap-absensi', 'rekap_absensi', 'rekap_data_pkl', 'rekap_problem', 'rekap_karir', 'rekap-karir'];

        if (in_array('BK', $roles, true)) {
            foreach ($allowedReadModules as $module) {
                if (str_contains($name, $module)) {
                    return $next($request);
                }
            }
        }

        if (in_array('tamu', $roles, true)) {
            $method = $request->method();
            if ($method !== 'GET' || $this->isRouteBlockedForTamu($name)) {
                return redirect()->back()->with('error', 'Role tamu hanya dapat melihat halaman.');
            }
            if ($this->isReadAllowedForTamu($name)) {
                return $next($request);
            }
            return redirect()->back()->with('error', 'Role tamu hanya dapat melihat halaman.');
        }

        $permissions = $this->getCachedPermissionNames($user);

        $permission = $this->resolvePermissionFromRouteName($name);
        if ($permission && in_array($permission, $permissions, true)) {
            return $next($request);
        }

        if (in_array($name, ['admin.dashboard', 'admin.ui', 'admin.forms', 'admin.responsive', 'admin.styles'], true) && $this->hasAnyAdminPermission($permissions)) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }

    private function getCachedRoles(User $user): array
    {
        if (isset(self::$roleCache[$user->id])) {
            return self::$roleCache[$user->id];
        }
        $roles = Cache::remember(AuthorizationCache::rolesKey($user->id), 600, fn () =>
            method_exists($user, 'getRoleNames') ? $user->getRoleNames()->toArray() : []
        );
        return self::$roleCache[$user->id] = array_values(array_filter($roles, fn ($r) => is_string($r) && $r !== ''));
    }

    private function getCachedPermissionNames(User $user): array
    {
        $permissions = Cache::remember(AuthorizationCache::permissionsKey($user->id), 600, fn () =>
            method_exists($user, 'getAllPermissions')
                ? $user->getAllPermissions()->pluck('name')->toArray()
                : []
        );
        return array_values(array_filter($permissions, fn ($p) => is_string($p) && $p !== ''));
    }

    private function isRouteBlockedForTamu(string $name): bool
    {
        $blocked = ['store', 'update', 'destroy', 'import', 'export', 'approve', 'reject', 'process', 'create', 'edit', 'print'];
        foreach ($blocked as $kw) {
            if ($name && str_contains($name, $kw)) {
                return true;
            }
        }
        return false;
    }

    private function isReadAllowedForTamu(string $name): bool
    {
        return ($name && str_ends_with($name, '.index')) || in_array($name, [
            'admin.dashboard', 'admin.profile', 'admin.ui', 'admin.forms', 'admin.responsive', 'admin.styles',
        ], true);
    }

    private function hasAnyAdminPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! is_string($permission)) continue;
            if (in_array($permission, ['view own profile', 'edit own profile'], true)) continue;
            return true;
        }
        return false;
    }

    private function resolvePermissionFromRouteName(string $routeName): ?string
    {
        if (isset(self::$permissionMap[$routeName])) {
            return self::$permissionMap[$routeName];
        }

        $result = $this->doResolvePermission($routeName);
        self::$permissionMap[$routeName] = $result;
        return $result;
    }

    private function doResolvePermission(string $routeName): ?string
    {
        $exactMap = [
            'admin.dashboard' => 'view dashboard',
            'admin.profile' => 'view own profile',
            'admin.input_data_pkl.input_pembimbing' => 'input_pembimbing.view',
            'admin.input_data_pkl.input_pembimbing.form' => 'input_pembimbing.create',
            'admin.input_data_pkl.input_pembimbing.show' => 'input_pembimbing.view',
            'admin.input_data_pkl.siswa_options' => 'input_data_pkl.view',
            'admin.input_data_pkl.bulk_set_pembimbing' => 'input_pembimbing.create',
            'admin.input_data_pkl.input_pembimbing.detach_bengkel' => 'input_pembimbing.delete',
            'admin.mutasi_pkl.siswa_options' => 'mutasi_pkl.view',
            'admin.rekap_problem.siswa_options' => 'rekap_problem.view',
            'admin.rekap_data_pkl.detail' => 'rekap_data_pkl.view',
            'admin.users.siswa.candidates' => 'user_siswa.view',
            'admin.ui' => 'view dashboard',
            'admin.forms' => 'view dashboard',
            'admin.responsive' => 'view dashboard',
            'admin.styles' => 'view dashboard',
        ];

        if (isset($exactMap[$routeName])) {
            return $exactMap[$routeName];
        }

        if (! str_starts_with($routeName, 'admin.')) {
            return null;
        }

        $parts = explode('.', $routeName);
        array_shift($parts);

        if (count($parts) < 2) {
            return null;
        }

        $action = array_pop($parts);
        $adminResource = implode('.', $parts);
        $resource = $this->mapResource($adminResource);

        $permissionAction = $this->mapAction($action);
        if (! $permissionAction) {
            return null;
        }

        return "{$resource}.{$permissionAction}";
    }

    private function mapResource(string $adminResource): string
    {
        $map = [
            'users' => 'data_admin',
            'roles' => 'role',
            'permissions' => 'permission',
            'users.pembimbing' => 'user_pembimbing',
            'users.siswa' => 'user_siswa',
            'surat.pengantar' => 'surat_pengantar',
            'cetak.p1' => 'cetak_p1',
        ];

        if (isset($map[$adminResource])) {
            return $map[$adminResource];
        }

        return str_replace(['-', '.'], ['_', '_'], $adminResource);
    }

    private function mapAction(string $action): ?string
    {
        return match (true) {
            in_array($action, ['index', 'show', 'template'], true) => 'view',
            in_array($action, ['create', 'store'], true) => 'create',
            in_array($action, ['edit', 'update', 'toggle'], true) || str_contains($action, 'terima') || str_contains($action, 'reset') || str_contains($action, 'password') => 'edit',
            $action === 'destroy' => 'delete',
            $action === 'import' => 'import',
            str_contains($action, 'export') => 'export',
            str_contains($action, 'bulk') => 'bulk_delete',
            in_array($action, ['print', 'cetak'], true) => 'print',
            in_array($action, ['process', 'approve', 'reject'], true) => $action,
            default => null,
        };
    }
}
