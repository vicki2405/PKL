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
        Schema::table('monitoring_details', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->integer('hadir')->default(0);
            $table->integer('izin')->default(0);
            $table->integer('sakit')->default(0);
            $table->integer('alpha')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitoring_details', function (Blueprint $table) {
            $table->dropColumn(['hadir', 'izin', 'sakit', 'alpha']);
            $table->enum('status', ['Hadir', 'Izin', 'Sakit', 'Alpha'])->default('Hadir');
        });
    }
};
