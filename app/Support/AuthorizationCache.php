<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class AuthorizationCache
{
    private const VERSION_KEY = 'authz.version';

    private static ?int $cachedVersion = null;

    public static function version(): int
    {
        if (self::$cachedVersion === null) {
            self::$cachedVersion = (int) Cache::get(self::VERSION_KEY, 0);
        }

        return self::$cachedVersion;
    }

    public static function rolesKey(int $userId): string
    {
        return "user:{$userId}:roles:v".self::version();
    }

    public static function permissionsKey(int $userId): string
    {
        return "user:{$userId}:permissions:v".self::version();
    }

    public static function profilePhotoKey(int $userId): string
    {
        return "user:{$userId}:profile_photo_url:v".self::version();
    }

    /**
     * Bust all cached roles/permissions/profile-photo URLs.
     */
    public static function bump(): void
    {
        // Note: Cache::increment() returns false on a missing key when using
        // the database driver, so a plain put() with an incremented value is used.
        $next = self::version() + 1;
        Cache::put(self::VERSION_KEY, $next, 86400);
        self::$cachedVersion = $next;
    }

    public static function resetLocal(): void
    {
        self::$cachedVersion = null;
    }
}
