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
            $table->date('tanggal_surat')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutasi_p_k_l_s', function (Blueprint $table) {
            $table->dropColumn('tanggal_surat');
        });
    }
};
