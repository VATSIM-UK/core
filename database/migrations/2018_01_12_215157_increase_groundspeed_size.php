<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncreaseGroundspeedSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE networkdata_pilots MODIFY current_groundspeed INT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('networkdata_pilots')
            ->where('current_groundspeed', '>', 16777215)
            ->update(['current_groundspeed' => 16777215]);
        DB::statement('ALTER TABLE networkdata_pilots MODIFY current_groundspeed MEDIUMINT UNSIGNED NULL');
    }
}
