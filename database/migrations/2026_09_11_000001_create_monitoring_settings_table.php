<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_settings', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_mulai_pkl')->nullable();
            $table->date('tanggal_selesai_pkl')->nullable();
            $table->string('periode_mode')->default('calendar');
            $table->json('monthly_periods')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_settings');
    }
};
