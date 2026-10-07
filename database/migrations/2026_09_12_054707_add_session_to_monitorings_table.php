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
        if (Schema::hasTable('monitorings') && ! Schema::hasColumn('monitorings', 'session')) {
            Schema::table('monitorings', function (Blueprint $table) {
                $table->unsignedTinyInteger('session')->default(1)->after('bengkel_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('monitorings') && Schema::hasColumn('monitorings', 'session')) {
            Schema::table('monitorings', function (Blueprint $table) {
                $table->dropColumn('session');
            });
        }
    }
};
