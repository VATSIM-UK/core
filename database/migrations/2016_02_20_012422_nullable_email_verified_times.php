<?php

use Illuminate\Database\Migrations\Migration;

class NullableEmailVerifiedTimes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `mship_account_email` MODIFY `verified_at` TIMESTAMP NULL DEFAULT NULL;');
        DB::update("UPDATE `mship_account_email`
                    SET `verified_at` = NULL
                    WHERE `verified_at` = '0000-00-00 00:00:00'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `mship_account_email` MODIFY `verified_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';");
        DB::update("UPDATE `mship_account_email`
                    SET `verified_at` = '0000-00-00 00:00:00'
                    WHERE `verified_at` IS NULL");
    }
}
