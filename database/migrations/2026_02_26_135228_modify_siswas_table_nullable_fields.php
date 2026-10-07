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
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('nisn')->nullable()->change();
            $table->string('jenis_kelamin', 1)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            // Cannot easily revert nullable to not null without data loss handling,
            // but for down we can try making them nullable false if we ensure data is there.
            // For now, let's just reverse the nullable change.
            // However, if there are null values, this will fail.
            // Since this is dev, we can try.
            $table->string('nisn')->nullable(false)->change();
            $table->string('jenis_kelamin', 1)->nullable(false)->change();
        });
    }
};
