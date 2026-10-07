<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $resources = [
            // Master Data
            'jurusan',
            'kelas',
            'siswa',
            'jabatan_pembimbing',
            'pembimbing',
            'bengkel',

            // Manajemen PKL
            'input_data_pkl',
            'input_pembimbing',
            'mutasi_pkl',
            'monitoring',
            'rekap_absensi',
            'rekap_absensi_pembekalan',
            'pembekalan_aktivasi',
            'cetak_p1',
            'surat_pengantar',
            'rekap_data_pkl',
            'informasi',
            'rekap_problem',
            'rekap_karir',

            // Manajemen User
            'user_pembimbing',
            'user_siswa',

            // Manajemen Admin
            'data_admin',
            'role',
            'permission',
            'backup',
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$resource}.{$action}", 'guard_name' => 'web']);
            }
        }

        // Additional specific permissions
        $miscPermissions = [
            'view dashboard',
            'view own profile',
            'edit own profile',

            // Import/Export/Bulk Actions
            'siswa.import',
            'siswa.export',
            'siswa.bulk_delete',
            'bengkel.import',
            'bengkel.bulk_delete',
            'input_data_pkl.import',
            'input_data_pkl.export',
            'input_data_pkl.bulk_delete',
            'mutasi_pkl.process',
            'mutasi_pkl.approve',
            'mutasi_pkl.reject',
            'user_pembimbing.import',
            'user_siswa.import',
            'user_siswa.bulk_delete',
            'backup.import',
            'backup.export',
            'rekap_absensi.export',
            'rekap_absensi_pembekalan.export',
            'rekap_absensi_pembekalan.print',
            'rekap_data_pkl.export',
            'rekap_problem.export_excel',
            'rekap_problem.export_pdf',
            'rekap_karir.export',
            'surat_pengantar.print',
            'cetak_p1.print',
        ];

        foreach ($miscPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Admin Role
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        // Super Admin Role
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        // Pembimbing Role
        $pembimbingRole = Role::firstOrCreate(['name' => 'pembimbing', 'guard_name' => 'web']);
        $pembimbingRole->syncPermissions(['view dashboard', 'view own profile', 'edit own profile', 'input_data_pkl.view', 'input_data_pkl.edit', 'informasi.view', 'pembekalan_aktivasi.view']);

        // Siswa Role
        $siswaRole = Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        $siswaRole->syncPermissions(['view own profile', 'edit own profile']);

        // Tamu Role (Optional, for guest access)
        Role::firstOrCreate(['name' => 'tamu', 'guard_name' => 'web']);

        // Bendahara Role
        $bendaharaRole = Role::firstOrCreate(['name' => 'bendahara', 'guard_name' => 'web']);
        $bendaharaRole->syncPermissions([
            'view dashboard',
            'view own profile',
            'edit own profile',
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
            'rekap_absensi_pembekalan.view',
            'pembekalan_aktivasi.view',
            'cetak_p1.view',
            'surat_pengantar.view',
            'informasi.view',
            'rekap_problem.view',
            'rekap_data_pkl.view',
            'rekap_data_pkl.export',
            'rekap_karir.view',
            'rekap_karir.export',
        ]);
    }
}
