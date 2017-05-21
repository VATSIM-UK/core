<?php

use Illuminate\Database\Migrations\Migration;

class AllowInfinityTrainingSpaces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `vt_facility` MODIFY `training_spaces` MEDIUMINT SIGNED NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE `vt_facility` MODIFY `training_spaces` MEDIUMINT UNSIGNED NOT NULL;');
    }
}
