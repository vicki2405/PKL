<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('monitoring_settings', 'tanggal_mulai_monitoring')) {
                $table->date('tanggal_mulai_monitoring')->nullable()->after('tanggal_mulai_pkl');
            }
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_settings', function (Blueprint $table) {
            if (Schema::hasColumn('monitoring_settings', 'tanggal_mulai_monitoring')) {
                $table->dropColumn('tanggal_mulai_monitoring');
            }
        });
    }
};
