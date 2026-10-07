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
        if (! Schema::hasTable('penempatan_p_k_l_s') || ! Schema::hasColumn('penempatan_p_k_l_s', 'pembimbing_id')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $fk = DB::selectOne("
            SELECT CONSTRAINT_NAME AS name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'penempatan_p_k_l_s'
              AND COLUMN_NAME = 'pembimbing_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");

        if ($fk && ! empty($fk->name)) {
            DB::statement("ALTER TABLE `penempatan_p_k_l_s` DROP FOREIGN KEY `{$fk->name}`");
        }

        DB::statement('ALTER TABLE `penempatan_p_k_l_s` MODIFY `pembimbing_id` BIGINT UNSIGNED NULL');

        $existingNamedFk = DB::selectOne("
            SELECT CONSTRAINT_NAME AS name
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'penempatan_p_k_l_s'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
              AND CONSTRAINT_NAME = 'penempatan_p_k_l_s_pembimbing_id_foreign'
            LIMIT 1
        ");

        if ($existingNamedFk && ! empty($existingNamedFk->name)) {
            DB::statement('ALTER TABLE `penempatan_p_k_l_s` DROP FOREIGN KEY `penempatan_p_k_l_s_pembimbing_id_foreign`');
        }

        DB::statement('
            ALTER TABLE `penempatan_p_k_l_s`
            ADD CONSTRAINT `penempatan_p_k_l_s_pembimbing_id_foreign`
            FOREIGN KEY (`pembimbing_id`) REFERENCES `pembimbings`(`id`) ON DELETE SET NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('penempatan_p_k_l_s') || ! Schema::hasColumn('penempatan_p_k_l_s', 'pembimbing_id')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $fallbackPembimbingId = DB::table('pembimbings')->value('id');
        if ($fallbackPembimbingId !== null) {
            DB::table('penempatan_p_k_l_s')
                ->whereNull('pembimbing_id')
                ->update(['pembimbing_id' => $fallbackPembimbingId]);
        }

        $fk = DB::selectOne("
            SELECT CONSTRAINT_NAME AS name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'penempatan_p_k_l_s'
              AND COLUMN_NAME = 'pembimbing_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");

        if ($fk && ! empty($fk->name)) {
            DB::statement("ALTER TABLE `penempatan_p_k_l_s` DROP FOREIGN KEY `{$fk->name}`");
        }

        DB::statement('ALTER TABLE `penempatan_p_k_l_s` MODIFY `pembimbing_id` BIGINT UNSIGNED NOT NULL');

        DB::statement('
            ALTER TABLE `penempatan_p_k_l_s`
            ADD CONSTRAINT `penempatan_p_k_l_s_pembimbing_id_foreign`
            FOREIGN KEY (`pembimbing_id`) REFERENCES `pembimbings`(`id`) ON DELETE CASCADE
        ');
    }
};
