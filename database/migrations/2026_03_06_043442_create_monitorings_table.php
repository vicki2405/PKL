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
        Schema::create('monitorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembimbing_id')->constrained('pembimbings')->onDelete('cascade');
            $table->foreignId('bengkel_id')->constrained('bengkels')->onDelete('cascade');
            $table->date('tanggal_kunjungan');
            $table->text('catatan_monitoring')->nullable();
            $table->string('foto_kegiatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitorings');
    }
};
