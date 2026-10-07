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
            $table->string('nama_bengkel_baru')->nullable()->after('bengkel_tujuan_id');
            $table->text('alamat_bengkel_baru')->nullable()->after('nama_bengkel_baru');
            $table->string('google_maps_link_baru')->nullable()->after('alamat_bengkel_baru');
            $table->unsignedBigInteger('bengkel_tujuan_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutasi_p_k_l_s', function (Blueprint $table) {
            $table->dropColumn(['nama_bengkel_baru', 'alamat_bengkel_baru', 'google_maps_link_baru']);
            $table->unsignedBigInteger('bengkel_tujuan_id')->nullable(false)->change();
        });
    }
};
