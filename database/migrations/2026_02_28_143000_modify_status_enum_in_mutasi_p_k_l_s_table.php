<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE mutasi_p_k_l_s MODIFY COLUMN status ENUM('pending', 'processing', 'approved', 'rejected') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE mutasi_p_k_l_s MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
    }
};
