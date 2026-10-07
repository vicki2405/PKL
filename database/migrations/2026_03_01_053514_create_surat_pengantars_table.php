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
        Schema::create('surat_pengantars', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat');
            $table->string('lampiran')->nullable();
            $table->string('tujuan_surat'); // Kepada Yth
            $table->text('alamat_tujuan');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->date('tanggal_surat');
            $table->string('kepala_sekolah_nama')->nullable();
            $table->string('kepala_sekolah_nbm')->nullable();
            $table->string('ketua_pkl_nama')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_pengantars');
    }
};
