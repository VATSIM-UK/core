<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sys_data_change')
            ->whereIn('data_key', [
                'last_login_ip',
                'discord_access_token',
                'discord_refresh_token',
                'vatsim_access_token',
                'vatsim_refresh_token',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
            ])
            ->delete();
    }

    public function down(): void
    {
        // Purged values cannot be restored.
    }
};
