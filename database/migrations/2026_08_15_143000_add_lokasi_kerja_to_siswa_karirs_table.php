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
        Schema::table('siswa_karirs', function (Blueprint $table) {
            $table->string('lokasi_kerja', 250)->nullable()->after('ekspektasi_gaji');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa_karirs', function (Blueprint $table) {
            $table->dropColumn('lokasi_kerja');
        });
    }
};
