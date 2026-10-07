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
            $table->dropColumn(['latitude', 'longitude']);
            $table->text('google_maps_link')->nullable()->after('deskripsi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bengkels', function (Blueprint $table) {
            $table->string('latitude')->nullable()->after('deskripsi');
            $table->string('longitude')->nullable()->after('latitude');
            $table->dropColumn('google_maps_link');
        });
    }
};
