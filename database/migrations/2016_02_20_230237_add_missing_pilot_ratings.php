<?php

use Illuminate\Database\Migrations\Migration;

class AddMissingPilotRatings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_qualification')->insert([
            ['code' => 'P6', 'type' => 'pilot', 'name_small' => 'P6', 'name_long' => 'P6', 'name_grp' => 'P6', 'vatsim' => 32, 'created_at' => DB::raw('NOW()'), 'updated_at' => DB::raw('NOW()')],
            ['code' => 'P7', 'type' => 'pilot', 'name_small' => 'P7', 'name_long' => 'P7', 'name_grp' => 'P7', 'vatsim' => 64, 'created_at' => DB::raw('NOW()'), 'updated_at' => DB::raw('NOW()')],
            ['code' => 'P8', 'type' => 'pilot', 'name_small' => 'P8', 'name_long' => 'P8', 'name_grp' => 'P8', 'vatsim' => 128, 'created_at' => DB::raw('NOW()'), 'updated_at' => DB::raw('NOW()')],
            ['code' => 'P9', 'type' => 'pilot', 'name_small' => 'P9', 'name_long' => 'P9', 'name_grp' => 'Pilot Flight Instructor', 'vatsim' => 256, 'created_at' => DB::raw('NOW()'), 'updated_at' => DB::raw('NOW()')],
        ]);
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
