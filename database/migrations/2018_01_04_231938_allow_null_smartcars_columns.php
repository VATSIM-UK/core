<?php

use Illuminate\Database\Migrations\Migration;

class AllowNullSmartcarsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `smartcars_pirep`
	CHANGE COLUMN `comments` `comments` MEDIUMTEXT NULL COLLATE \'utf8mb4_unicode_ci\' AFTER `landing_rate`');
        DB::statement('ALTER TABLE `smartcars_posrep`
	CHANGE COLUMN `time_departure` `time_departure` TIME NULL AFTER `phase`');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE `smartcars_pirep`
	CHANGE COLUMN `comments` `comments` MEDIUMTEXT NOT NULL COLLATE \'utf8mb4_unicode_ci\' AFTER `landing_rate`');
        DB::statement('ALTER TABLE `smartcars_posrep`
	CHANGE COLUMN `time_departure` `time_departure` TIME NOT NULL AFTER `phase`');
    }
}
