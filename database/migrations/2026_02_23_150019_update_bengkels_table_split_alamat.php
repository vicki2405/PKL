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
        if (Schema::hasColumn('bengkels', 'alamat')) {
            Schema::table('bengkels', function (Blueprint $table) {
                $table->dropColumn('alamat');
            });
        }

        Schema::table('bengkels', function (Blueprint $table) {
            if (!Schema::hasColumn('bengkels', 'dusun')) {
                $table->string('dusun')->nullable()->after('nama_pemilik');
            }
            if (!Schema::hasColumn('bengkels', 'desa')) {
                $table->string('desa')->nullable()->after('dusun');
            }
            if (!Schema::hasColumn('bengkels', 'kecamatan')) {
                $table->string('kecamatan')->nullable()->after('desa');
            }
            if (!Schema::hasColumn('bengkels', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('kecamatan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bengkels', function (Blueprint $table) {
            if (!Schema::hasColumn('bengkels', 'alamat')) {
                $table->string('alamat')->nullable()->after('nama_pemilik');
            }

            $columnsToDrop = array_values(array_filter(
                ['dusun', 'desa', 'kecamatan', 'deskripsi'],
                fn (string $column) => Schema::hasColumn('bengkels', $column)
            ));

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
