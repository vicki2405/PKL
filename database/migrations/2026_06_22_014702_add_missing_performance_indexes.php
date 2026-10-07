<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring_details', function (Blueprint $table) {
            $table->index('siswa_id');
            $table->index('is_pelanggaran');
            $table->index(['monitoring_id', 'is_pelanggaran']);
        });

        Schema::table('penempatan_p_k_l_s', function (Blueprint $table) {
            $table->index('siswa_id');
        });

        Schema::table('monitorings', function (Blueprint $table) {
            $table->index(['pembimbing_id', 'bengkel_id']);
            $table->index(['bengkel_id', 'tanggal_kunjungan']);
        });

        Schema::table('surat_pengantar_siswa', function (Blueprint $table) {
            $table->index('siswa_id');
            $table->index('surat_pengantar_id');
        });

        Schema::table('activity_log', function (Blueprint $table) {
            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->index('kelas_id');
        });

        Schema::table('pembimbings', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('mutasi_p_k_l_s', function (Blueprint $table) {
            $table->index('siswa_id');
            $table->index(['siswa_id', 'status']);
        });

        Schema::table('surat_pengantars', function (Blueprint $table) {
            $table->index('tanggal_surat');
        });

        Schema::table('rekap_problems', function (Blueprint $table) {
            $table->index('siswa_id');
            $table->index('bengkel_id');
            $table->index('pembimbing_id');
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_details', function (Blueprint $table) {
            $table->dropIndex(['siswa_id']);
            $table->dropIndex(['is_pelanggaran']);
            $table->dropIndex(['monitoring_id', 'is_pelanggaran']);
        });

        Schema::table('penempatan_p_k_l_s', function (Blueprint $table) {
            $table->dropIndex(['siswa_id']);
        });

        Schema::table('monitorings', function (Blueprint $table) {
            $table->dropIndex(['pembimbing_id', 'bengkel_id']);
            $table->dropIndex(['bengkel_id', 'tanggal_kunjungan']);
        });

        Schema::table('surat_pengantar_siswa', function (Blueprint $table) {
            $table->dropIndex(['siswa_id']);
            $table->dropIndex(['surat_pengantar_id']);
        });

        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex(['subject_type', 'subject_id']);
            $table->dropIndex(['causer_type', 'causer_id']);
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropIndex(['kelas_id']);
        });

        Schema::table('pembimbings', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('mutasi_p_k_l_s', function (Blueprint $table) {
            $table->dropIndex(['siswa_id']);
            $table->dropIndex(['siswa_id', 'status']);
        });

        Schema::table('surat_pengantars', function (Blueprint $table) {
            $table->dropIndex(['tanggal_surat']);
        });

        Schema::table('rekap_problems', function (Blueprint $table) {
            $table->dropIndex(['siswa_id']);
            $table->dropIndex(['bengkel_id']);
            $table->dropIndex(['pembimbing_id']);
        });
    }
};
