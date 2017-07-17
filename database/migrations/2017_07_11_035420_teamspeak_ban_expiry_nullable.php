<?php

use Illuminate\Database\Migrations\Migration;

class TeamspeakBanExpiryNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE teamspeak_ban MODIFY expires_at TIMESTAMP NULL DEFAULT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE teamspeak_ban MODIFY expires_at TIMESTAMP NOT NULL DEFAULT 0');
    }
}
