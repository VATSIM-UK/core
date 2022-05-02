<?php

use Illuminate\Database\Migrations\Migration;

class AddP0Rating extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_qualification')->insert(
            ['code' => 'P0', 'type' => 'pilot', 'name_small' => 'P0', 'name_long' => 'P0', 'name_grp' => 'P0', 'vatsim' => 0]
        );
    }
}
