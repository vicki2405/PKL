<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembimbings', function (Blueprint $table) {
            $table->string('nip')->nullable()->after('nama_guru');
            $table->string('jenis_kelamin')->default('L')->after('nip');
            $table->text('alamat')->nullable()->after('jenis_kelamin');
            $table->string('no_hp')->nullable()->after('alamat');
        });
    }

    public function down(): void
    {
        Schema::table('pembimbings', function (Blueprint $table) {
            $table->dropColumn(['nip', 'jenis_kelamin', 'alamat', 'no_hp']);
        });
    }
};
