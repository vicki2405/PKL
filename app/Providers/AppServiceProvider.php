<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Support\AuthorizationCache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Bust per-user roles/permissions caches whenever authorization data changes.
        Event::listen([
            'eloquent.attached: model_has_roles',
            'eloquent.detached: model_has_roles',
            'eloquent.synced: model_has_roles',
            'eloquent.attached: model_has_permissions',
            'eloquent.detached: model_has_permissions',
            'eloquent.synced: model_has_permissions',
            'eloquent.attached: role_has_permissions',
            'eloquent.detached: role_has_permissions',
            'eloquent.synced: role_has_permissions',
        ], fn () => AuthorizationCache::bump());

        Role::saved(fn () => AuthorizationCache::bump());
        Role::deleted(fn () => AuthorizationCache::bump());
        Permission::saved(fn () => AuthorizationCache::bump());
        Permission::deleted(fn () => AuthorizationCache::bump());

        Gate::before(function ($user, $ability) {
            if ($user instanceof \App\Models\User && $user->hasAnyRole(['admin', 'super-admin'])) {
                return true;
            }

            return null;
        });

        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            if (! Cache::has('permissions.ensure.rekap_problem_export')) {
                if (Schema::hasTable('permissions')) {
                    $permissionsToEnsure = [
                        'input_data_pkl.export',
                        'rekap_problem.export_excel',
                        'rekap_problem.export_pdf',
                        'rekap_karir.export_excel',
                        'rekap_karir.export_pdf',
                    ];

                    $created = false;
                    foreach ($permissionsToEnsure as $permissionName) {
                        $permission = Permission::firstOrCreate([
                            'name' => $permissionName,
                            'guard_name' => 'web',
                        ]);
                        $created = $created || $permission->wasRecentlyCreated;
                    }

                    if ($created) {
                        app(PermissionRegistrar::class)->forgetCachedPermissions();
                    }

                    Cache::put('permissions.ensure.rekap_problem_export', true, 86400);
                }
            }
        } catch (\Throwable $th) {
            report($th);
        }
    }
}
