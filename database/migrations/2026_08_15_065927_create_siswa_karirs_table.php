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
        Schema::create('siswa_karirs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('siswa_id')->unique()->constrained('siswas')->onDelete('cascade');
            $table->enum('tipe_karir', ['kuliah', 'kerja', 'wirausaha']);

            // Kuliah Columns
            $table->string('nama_universitas', 150)->nullable();
            $table->string('status_universitas', 50)->nullable();
            $table->string('jenjang_studi', 10)->nullable();
            $table->string('program_studi', 100)->nullable();
            $table->string('jalur_masuk', 50)->nullable();
            $table->integer('tahun_mulai')->nullable();

            // Kerja Columns
            $table->string('nama_perusahaan', 150)->nullable();
            $table->string('posisi_kerja', 100)->nullable();
            $table->string('jenis_pekerjaan', 50)->nullable();
            $table->string('kesesuaian_jurusan', 50)->nullable();
            $table->string('target_mulai_kerja', 50)->nullable();
            $table->string('ekspektasi_gaji', 30)->nullable();

            // Wirausaha Columns
            $table->string('nama_usaha', 150)->nullable();
            $table->string('sektor_usaha', 50)->nullable();
            $table->string('status_kepemilikan', 50)->nullable();
            $table->string('modal_awal', 30)->nullable();
            $table->string('lokasi_usaha', 250)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa_karirs');
    }
};
