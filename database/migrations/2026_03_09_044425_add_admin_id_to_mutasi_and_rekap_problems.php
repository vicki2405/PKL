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
        Schema::table('mutasi_p_k_l_s', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('rekap_problems', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutasi_p_k_l_s', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });

        Schema::table('rekap_problems', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });
    }
};
