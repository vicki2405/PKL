<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use App\Models\SiswaUser;
use App\Models\User;
use Illuminate\Console\Command;

class RebuildUsers extends Command
{
    protected $signature = 'users:rebuild';

    protected $description = 'Rebuild user accounts for Siswa and Pembimbing from master data';

    public function handle(): int
    {
        $this->info('Linking existing Siswa accounts only (no auto-create)...');
        $countSiswa = 0;
        Siswa::whereNull('user_id')
            ->where('user_account_enabled', true)
            ->orderBy('id')
            ->chunkById(200, function ($siswas) use (&$countSiswa) {
                foreach ($siswas as $siswa) {
                    if (! $siswa->nisn) {
                        continue;
                    }

                    $email = $siswa->nisn.'@siswa.sekolah.com';

                    $user = User::where('email', $email)->first();
                    if (! $user) {
                        continue;
                    }

                    if (empty($user->username)) {
                        $user->update(['username' => $siswa->nisn]);
                    }

                    $siswa->update(['user_id' => $user->id]);
                    $countSiswa++;
                }
            });
        $this->info('Siswa legacy linked (existing only): '.$countSiswa);

        $siswaAccounts = 0;
        Siswa::where('user_account_enabled', true)
            ->whereNull('siswa_user_id')
            ->orderBy('id')
            ->chunkById(200, function ($siswas) use (&$siswaAccounts) {
                foreach ($siswas as $siswa) {
                    if ($siswa->user_account_enabled === false || ! $siswa->nisn) {
                        continue;
                    }

                    $su = SiswaUser::where('username', $siswa->nisn)->first();
                    if (! $su) {
                        $su = SiswaUser::where('email', $siswa->nisn.'@siswa.sekolah.com')->first();
                    }

                    if (! $su) {
                        continue;
                    }

                    $siswa->update(['siswa_user_id' => $su->id]);
                    $siswaAccounts++;
                }
            });
        $this->info('Siswa dedicated linked (existing only): '.$siswaAccounts);

        $this->info('Pembimbing auto-create/link skipped (to prevent resurrecting deleted accounts).');

        $this->info('Done');

        return self::SUCCESS;
    }
}
