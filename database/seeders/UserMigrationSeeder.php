<?php

namespace Database\Seeders;

use App\Models\Pembimbing;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Migrate Siswa
        Siswa::whereNull('user_id')
            ->get()
            ->each(function (Siswa $siswa) {
                $email = $siswa->nisn.'@siswa.sekolah.com';
                $password = $siswa->nisn;

                $user = User::where('email', $email)->first();
                if (! $user) {
                    $user = User::create([
                        'name' => $siswa->nama,
                        'email' => $email,
                        'username' => $siswa->nisn,
                        'password' => Hash::make($password),
                    ]);
                    $user->assignRole('siswa');
                } else {
                    if (empty($user->username)) {
                        $user->update(['username' => $siswa->nisn]);
                    }
                }

                $siswa->update(['user_id' => $user->id]);
                $this->command->info("Migrated Siswa: {$siswa->nama}");
            });

        // Migrate Pembimbing
        Pembimbing::whereNull('user_id')
            ->get()
            ->each(function (Pembimbing $pembimbing) {
                $email = Str::slug($pembimbing->nama_guru).'@pembimbing.com';

                $count = User::where('email', 'like', $email.'%')->count();
                if ($count > 0) {
                    $email = Str::slug($pembimbing->nama_guru).($count + 1).'@pembimbing.com';
                }

                $user = User::where('email', $email)->first();
                if (! $user) {
                    $base = Str::slug($pembimbing->nama_guru);
                    $username = $base;
                    $i = 1;
                    while (User::where('username', $username)->exists()) {
                        $username = $base.'-'.$i;
                        $i++;
                    }
                    $user = User::create([
                        'name' => $pembimbing->nama_guru,
                        'email' => $email,
                        'username' => $username,
                        'password' => Hash::make('password'),
                    ]);
                    $user->assignRole('pembimbing');
                } else {
                    if (empty($user->username)) {
                        $base = Str::slug($pembimbing->nama_guru);
                        $username = $base;
                        $i = 1;
                        while (User::where('username', $username)->where('id', '!=', $user->id)->exists()) {
                            $username = $base.'-'.$i;
                            $i++;
                        }
                        $user->update(['username' => $username]);
                    }
                }

                $pembimbing->update(['user_id' => $user->id]);
                $this->command->info("Migrated Pembimbing: {$pembimbing->nama_guru}");
            });
    }
}
