<?php

namespace App\Console\Commands;

use App\Models\Pembimbing;
use App\Models\PembimbingUser;
use App\Models\User;
use Illuminate\Console\Command;

class CleanPembimbingData extends Command
{
    protected $signature = 'pembimbing:clean';

    protected $description = 'Bersihkan data pembimbing yang corrupt atau tidak sinkron dari import gagal';

    public function handle()
    {
        $this->info('Membersihkan data pembimbing yang tidak sinkron...');

        // 1. Cari PembimbingUser yang tidak ada User sistemnya
        $orphanedPembimbingUsers = PembimbingUser::all()
            ->filter(function ($user) {
                $pembimbing = Pembimbing::where('pembimbing_user_id', $user->id)->first();
                return $pembimbing && !$pembimbing->user_id;
            });

        foreach ($orphanedPembimbingUsers as $pUser) {
            $this->warn("PembimbingUser {$pUser->name} ({$pUser->email}) tidak memiliki User sistem");
            if ($this->confirm("Hapus {$pUser->name}?", false)) {
                $pembimbing = Pembimbing::where('pembimbing_user_id', $pUser->id)->first();
                if ($pembimbing) {
                    $pembimbing->update(['pembimbing_user_id' => null]);
                }
                $pUser->delete();
                $this->line("✓ Dihapus: {$pUser->name}");
            }
        }

        // 2. Cari Pembimbing yang linked tetapi data tidak valid
        $invalidPembimbings = Pembimbing::whereNotNull('pembimbing_user_id')
            ->whereNull('user_id')
            ->get();

        foreach ($invalidPembimbings as $p) {
            $this->warn("Pembimbing {$p->nama_guru} memiliki pembimbing_user_id tapi tidak ada user_id");
            if ($this->confirm("Perbaiki?", true)) {
                // Coba buat User baru untuk pembimbing ini
                $pUser = PembimbingUser::find($p->pembimbing_user_id);
                if ($pUser) {
                    $sysUser = User::create([
                        'name' => $pUser->name,
                        'email' => $pUser->email,
                        'username' => $pUser->username . '-sys',
                        'password' => $pUser->password,
                    ]);
                    $sysUser->assignRole('pembimbing');
                    $p->update(['user_id' => $sysUser->id]);
                    $this->line("✓ User sistem dibuat untuk {$p->nama_guru}");
                }
            }
        }

        // 3. Cari username yang duplicate
        $this->info('Memeriksa username yang duplicate...');
        $duplicateUsernames = PembimbingUser::groupBy('username')
            ->havingRaw('count(*) > 1')
            ->pluck('username')
            ->toArray();

        foreach ($duplicateUsernames as $username) {
            $users = PembimbingUser::where('username', $username)->get();
            $this->warn("Username '{$username}' dimiliki oleh {$users->count()} user");
            foreach ($users->skip(1) as $user) {
                if ($this->confirm("Generate username baru untuk {$user->name}?", true)) {
                    $newUsername = $username . '-' . uniqid();
                    $user->update(['username' => $newUsername]);
                    $this->line("✓ Username berubah menjadi: {$newUsername}");
                }
            }
        }

        $this->info('Pembersihan data selesai!');
    }
}
