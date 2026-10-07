<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mutasi_p_k_l_s', function (Blueprint $table) {
            $table->string('nama_pemilik_bengkel_baru')->nullable()->after('nama_bengkel_baru');
        });
    }

    public function down(): void
    {
        Schema::table('mutasi_p_k_l_s', function (Blueprint $table) {
            $table->dropColumn('nama_pemilik_bengkel_baru');
        });
    }
};
