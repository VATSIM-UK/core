<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<'SQL'
            UPDATE training_places tp
            INNER JOIN training_waiting_list_account wla
                ON tp.waiting_list_account_id = wla.id
            SET tp.account_id = wla.account_id
            WHERE tp.account_id IS NULL
              AND tp.waiting_list_account_id IS NOT NULL
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: this migration is a data backfill.
    }
};
