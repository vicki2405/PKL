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
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('agama')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->string('dusun')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn(['agama', 'rt', 'rw', 'dusun', 'kelurahan', 'kecamatan']);
        });
    }
};
