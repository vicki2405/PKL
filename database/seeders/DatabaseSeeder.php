<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);

        try {
            $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

            $superAdminUser = User::updateOrCreate(
                ['email' => 'superadmin@gmail.com'],
                [
                    'name' => 'Super Admin',
                    'username' => 'superadmin',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                ]
            );
            $superAdminUser->syncRoles([$superAdminRole->name]);

            $adminUser = User::updateOrCreate(
                ['email' => 'admin@gmail.com'],
                [
                    'name' => 'Admin',
                    'username' => 'admin',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                ]
            );
            $adminUser->syncRoles([$adminRole->name]);

            if ($superAdminUser && ! $superAdminUser->hasRole($superAdminRole->name)) {
                $superAdminUser->assignRole($superAdminRole);
            }

            if ($adminUser && ! $adminUser->hasRole($adminRole->name)) {
                $adminUser->assignRole($adminRole);
            }
        } catch (\Throwable $e) {
        }
    }
}
