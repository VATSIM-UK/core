<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

return new class extends Migration
{
    public function up(): void
    {
        // Safety net: populate any remaining NULLs from waiting list provenance (if present).
        DB::statement(<<<'SQL'
            UPDATE training_places tp
            INNER JOIN training_waiting_list_account wla
                ON tp.waiting_list_account_id = wla.id
            SET tp.account_id = wla.account_id
            WHERE tp.account_id IS NULL
              AND tp.waiting_list_account_id IS NOT NULL
        SQL);

        if (DB::table('training_places')->whereNull('account_id')->exists()) {
            throw new RuntimeException(
                'Cannot make training_places.account_id NOT NULL: rows still exist with NULL account_id. '.
                'Fix or backfill these records before re-running this migration.'
            );
        }

        $this->dropAccountForeignKeyIfExists();

        DB::statement('ALTER TABLE `training_places` MODIFY `account_id` INT UNSIGNED NOT NULL');

        Schema::table('training_places', function (Blueprint $table) {
            $table->foreign('account_id')
                ->references('id')
                ->on('mship_account')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        $this->dropAccountForeignKeyIfExists();

        DB::statement('ALTER TABLE `training_places` MODIFY `account_id` INT UNSIGNED NULL');

        Schema::table('training_places', function (Blueprint $table) {
            $table->foreign('account_id')
                ->references('id')
                ->on('mship_account')
                ->nullOnDelete();
        });
    }

    private function dropAccountForeignKeyIfExists(): void
    {
        $constraintName = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'training_places')
            ->where('COLUMN_NAME', 'account_id')
            ->where('REFERENCED_TABLE_NAME', 'mship_account')
            ->value('CONSTRAINT_NAME');

        if (! is_string($constraintName) || $constraintName === '') {
            return;
        }

        DB::statement('ALTER TABLE `training_places` DROP FOREIGN KEY `'.$constraintName.'`');
    }
};
