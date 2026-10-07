<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembimbings', function (Blueprint $table) {
            $table->foreignId('pembimbing_user_id')->nullable()->after('user_id')->constrained('pembimbing_users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pembimbings', function (Blueprint $table) {
            $table->dropForeign(['pembimbing_user_id']);
            $table->dropColumn('pembimbing_user_id');
        });
    }
};
