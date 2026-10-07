<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove any duplicate placements for the same student (keep the latest one)
        // so the unique index can be created safely.
        $duplicates = DB::table('penempatan_p_k_l_s as p')
            ->select('p.id')
            ->joinSub(
                DB::table('penempatan_p_k_l_s')
                    ->select('siswa_id')
                    ->selectRaw('MAX(id) as max_id')
                    ->groupBy('siswa_id')
                    ->havingRaw('COUNT(*) > 1'),
                'd',
                function ($join) {
                    $join->on('p.siswa_id', '=', 'd.siswa_id')
                        ->on('p.id', '<>', 'd.max_id');
                }
            )
            ->pluck('id');

        if ($duplicates->isNotEmpty()) {
            DB::table('penempatan_p_k_l_s')
                ->whereIn('id', $duplicates)
                ->delete();
        }

        Schema::table('penempatan_p_k_l_s', function (Blueprint $table) {
            $table->unique('siswa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penempatan_p_k_l_s', function (Blueprint $table) {
            $table->dropUnique(['siswa_id']);
        });
    }
};
