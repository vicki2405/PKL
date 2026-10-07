<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->foreignId('siswa_user_id')->nullable()->after('user_account_enabled')->constrained('siswa_users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropForeign(['siswa_user_id']);
            $table->dropColumn('siswa_user_id');
        });
    }
};
