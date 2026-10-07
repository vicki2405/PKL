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
        Schema::create('surat_pengantar_template_settings', function (Blueprint $table) {
            $table->id();
            $table->string('default_nomor_surat')->nullable();
            $table->string('default_lampiran')->nullable();
            $table->string('default_perihal')->nullable();
            $table->date('default_tanggal_mulai')->nullable();
            $table->date('default_tanggal_selesai')->nullable();
            $table->string('kepala_sekolah_nama')->nullable();
            $table->string('kepala_sekolah_nbm')->nullable();
            $table->string('kepala_sekolah_ttd_path')->nullable();
            $table->string('kepala_sekolah_stempel_path')->nullable();
            $table->string('ketua_pkl_nama')->nullable();
            $table->string('ketua_pkl_nbm')->nullable();
            $table->string('ketua_pkl_ttd_path')->nullable();
            $table->string('ketua_pkl_stempel_path')->nullable();
            $table->string('kop_surat_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_pengantar_template_settings');
    }
};
