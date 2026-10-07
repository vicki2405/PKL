<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->index('nama');
            $table->index('nipd');
        });

        Schema::table('bengkels', function (Blueprint $table) {
            $table->index('nama_bengkel');
            $table->index('kode_bengkel');
            $table->index('dusun');
            $table->index('desa');
            $table->index('kecamatan');
            $table->index('latitude');
            $table->index('longitude');
        });

        Schema::table('pembimbings', function (Blueprint $table) {
            $table->index('nama_guru');
            $table->index('nip');
        });

        Schema::table('monitorings', function (Blueprint $table) {
            $table->index('tanggal_kunjungan');
        });

        Schema::table('mutasi_p_k_l_s', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('rekap_problems', function (Blueprint $table) {
            $table->index('tanggal');
        });

        Schema::table('informasis', function (Blueprint $table) {
            $table->index('tanggal_terbit');
            $table->index('status');
        });

        Schema::table('penempatan_p_k_l_s', function (Blueprint $table) {
            $table->index(['bengkel_id', 'pembimbing_id']);
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->index('nama_kelas');
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropIndex(['nama']);
            $table->dropIndex(['nipd']);
        });

        Schema::table('bengkels', function (Blueprint $table) {
            $table->dropIndex(['nama_bengkel']);
            $table->dropIndex(['kode_bengkel']);
            $table->dropIndex(['dusun']);
            $table->dropIndex(['desa']);
            $table->dropIndex(['kecamatan']);
            $table->dropIndex(['latitude']);
            $table->dropIndex(['longitude']);
        });

        Schema::table('pembimbings', function (Blueprint $table) {
            $table->dropIndex(['nama_guru']);
            $table->dropIndex(['nip']);
        });

        Schema::table('monitorings', function (Blueprint $table) {
            $table->dropIndex(['tanggal_kunjungan']);
        });

        Schema::table('mutasi_p_k_l_s', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('rekap_problems', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
        });

        Schema::table('informasis', function (Blueprint $table) {
            $table->dropIndex(['tanggal_terbit']);
            $table->dropIndex(['status']);
        });

        Schema::table('penempatan_p_k_l_s', function (Blueprint $table) {
            $table->dropIndex(['bengkel_id', 'pembimbing_id']);
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropIndex(['nama_kelas']);
        });
    }
};
