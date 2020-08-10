<?php

use Illuminate\Database\Migrations\Migration;

class ChangeOrderOfDiscordColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `mship_account` MODIFY COLUMN `discord_id` varchar(30) AFTER `remember_token`');
        DB::statement('ALTER TABLE `mship_account` MODIFY COLUMN `discord_access_token` text AFTER `discord_id`');
        DB::statement('ALTER TABLE `mship_account` MODIFY COLUMN `discord_refresh_token` text AFTER `discord_access_token`');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
