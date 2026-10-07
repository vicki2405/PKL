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
            // Ubah latitude dan longitude menjadi tipe double agar bisa menampung nilai yang lebih fleksibel
            $table->double('latitude')->nullable()->change();
            $table->double('longitude')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bengkels', function (Blueprint $table) {
            // Kembalikan ke decimal(11,8) jika rollback
            $table->decimal('latitude', 11, 8)->nullable()->change();
            $table->decimal('longitude', 11, 8)->nullable()->change();
        });
    }
};
