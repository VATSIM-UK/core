<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('vt_application')->where('status', 15)->select('id')
            ->chunkById(500, function ($applications) {
                DB::table('vt_application')->where('status', 15)
                    ->whereIn('id', $applications->pluck('id'))->delete();
            });
    }

    public function down(): void
    {
        // Deleted applications cannot be restored.
    }
};
