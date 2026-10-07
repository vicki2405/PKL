<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->index('nisn');
        });

        Schema::table('monitorings', function (Blueprint $table) {
            $table->index('laporan_diterima');
        });

        Schema::table('pembekalan_attendances', function (Blueprint $table) {
            $table->index('siswa_id');
        });

        Schema::table('pembekalan_aktivasis', function (Blueprint $table) {
            $table->index('created_by');
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropIndex(['nisn']);
        });

        Schema::table('monitorings', function (Blueprint $table) {
            $table->dropIndex(['laporan_diterima']);
        });

        Schema::table('pembekalan_attendances', function (Blueprint $table) {
            $table->dropIndex(['siswa_id']);
        });

        Schema::table('pembekalan_aktivasis', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['tanggal']);
        });
    }
};
