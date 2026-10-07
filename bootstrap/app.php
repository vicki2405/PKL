<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'admin.write' => \App\Http\Middleware\AdminWriteAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            $user = $request->user();
            if ($user && $user->hasRole('siswa')) {
                return redirect()->route('siswa.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }
            if ($user && $user->hasRole('pembimbing')) {
                return redirect()->route('pembimbing.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }

            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        });
    })->create();
