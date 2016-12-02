<?php

use Illuminate\Database\Migrations\Migration;

class DeletedOldUnexpiredAtcSessions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('networkdata_atc')
            ->whereNull('disconnected_at')
            ->where('connected_at', '<=', \Carbon\Carbon::now()->subDays(1))
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Sadly, no return from this one!
    }
}
