<?php

use Illuminate\Database\Migrations\Migration;

class LengthenPilotGroundspeed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE networkdata_pilots MODIFY current_groundspeed MEDIUMINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('networkdata_pilots')
            ->where('current_groundspeed', '>', 65535)
            ->update(['current_groundspeed' => 65535]);
        DB::statement('ALTER TABLE networkdata_pilots MODIFY current_groundspeed SMALLINT UNSIGNED NULL');
    }
}
