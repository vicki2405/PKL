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
        Schema::create('rekap_problems', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('bengkel_id')->constrained('bengkels')->onDelete('cascade');
            $table->foreignId('pembimbing_id')->constrained('pembimbings')->onDelete('cascade');
            $table->text('deskripsi_masalah');
            $table->text('deskripsi_tindakan');
            $table->string('saksi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_problems');
    }
};
