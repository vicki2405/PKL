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
            $table->dropColumn('alamat_bengkel_baru');
            $table->string('dusun')->nullable()->after('nama_bengkel_baru');
            $table->string('desa')->nullable()->after('dusun');
            $table->string('kecamatan')->nullable()->after('desa');
            $table->text('deskripsi')->nullable()->after('kecamatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutasi_p_k_l_s', function (Blueprint $table) {
            $table->text('alamat_bengkel_baru')->nullable()->after('nama_bengkel_baru');
            $table->dropColumn(['dusun', 'desa', 'kecamatan', 'deskripsi']);
        });
    }
};
