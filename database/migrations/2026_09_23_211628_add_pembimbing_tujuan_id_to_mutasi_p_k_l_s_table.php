<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mutasi_p_k_l_s', function (Blueprint $table) {
            $table->foreignId('pembimbing_tujuan_id')
                ->nullable()
                ->after('pembimbing_id')
                ->constrained('pembimbings')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutasi_p_k_l_s', function (Blueprint $table) {
            $table->dropForeign(['pembimbing_tujuan_id']);
            $table->dropColumn('pembimbing_tujuan_id');
        });
    }
};
