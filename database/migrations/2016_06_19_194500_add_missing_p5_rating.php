<?php

use Illuminate\Database\Migrations\Migration;

class AddMissingP5Rating extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::table('mship_qualification')->where('code', 'P5')->count() == 0) {
            DB::table('mship_qualification')->insert([
                ['code' => 'P5', 'type' => 'pilot', 'name_small' => 'P5', 'name_long' => 'P5', 'name_grp' => 'P5', 'vatsim' => 16, 'created_at' => DB::raw('NOW()'), 'updated_at' => DB::raw('NOW()')],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nothing.
    }
}
