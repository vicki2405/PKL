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
        Schema::table('penempatan_p_k_l_s', function (Blueprint $table) {
            $table->dropForeign(['pembimbing_id']);
        });

        Schema::table('penempatan_p_k_l_s', function (Blueprint $table) {
            $table->unsignedBigInteger('pembimbing_id')->nullable()->change();
        });

        Schema::table('penempatan_p_k_l_s', function (Blueprint $table) {
            $table->foreign('pembimbing_id')
                ->references('id')
                ->on('pembimbings')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $fallbackPembimbingId = DB::table('pembimbings')->value('id');
        if ($fallbackPembimbingId !== null) {
            DB::table('penempatan_p_k_l_s')
                ->whereNull('pembimbing_id')
                ->update(['pembimbing_id' => $fallbackPembimbingId]);
        }

        Schema::table('penempatan_p_k_l_s', function (Blueprint $table) {
            $table->dropForeign(['pembimbing_id']);
        });

        Schema::table('penempatan_p_k_l_s', function (Blueprint $table) {
            $table->unsignedBigInteger('pembimbing_id')->nullable(false)->change();
        });

        Schema::table('penempatan_p_k_l_s', function (Blueprint $table) {
            $table->foreign('pembimbing_id')
                ->references('id')
                ->on('pembimbings')
                ->cascadeOnDelete();
        });
    }
};
