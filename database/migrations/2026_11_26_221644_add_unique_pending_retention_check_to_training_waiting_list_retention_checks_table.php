<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Keep the newest pending check per account, expire the rest.
        $keepIds = DB::table('training_waiting_list_retention_checks')
            ->where('status', 'pending')
            ->groupBy('waiting_list_account_id')
            ->selectRaw('max(id) as id')
            ->pluck('id');

        DB::table('training_waiting_list_retention_checks')
            ->where('status', 'pending')
            ->whereNotIn('id', $keepIds)
            ->update(['status' => 'expired', 'updated_at' => now()]);

        DB::statement("
            CREATE UNIQUE INDEX retention_checks_one_pending_per_account
            ON training_waiting_list_retention_checks (
                waiting_list_account_id,
                (CASE WHEN status = 'pending' THEN 1 END)
            )
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX retention_checks_one_pending_per_account ON training_waiting_list_retention_checks');
    }
};
