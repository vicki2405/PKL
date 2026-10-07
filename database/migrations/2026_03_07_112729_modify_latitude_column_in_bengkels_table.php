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
        Schema::table('bengkels', function (Blueprint $table) {
            // Ubah latitude menjadi decimal(11,8) agar bisa menampung nilai longitude jika tertukar
            $table->decimal('latitude', 11, 8)->nullable()->change();
            // Longitude tetap 11,8
            $table->decimal('longitude', 11, 8)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bengkels', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->change();
            $table->decimal('longitude', 11, 8)->nullable()->change();
        });
    }
};
