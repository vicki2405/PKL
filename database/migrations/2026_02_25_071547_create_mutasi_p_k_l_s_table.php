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
        Schema::create('mutasi_p_k_l_s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('bengkel_asal_id')->constrained('bengkels')->cascadeOnDelete();
            $table->foreignId('bengkel_tujuan_id')->constrained('bengkels')->cascadeOnDelete();
            $table->foreignId('pembimbing_id')->constrained('pembimbings')->cascadeOnDelete(); // Pembimbing yang mengajukan
            $table->text('alasan');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_p_k_l_s');
    }
};
