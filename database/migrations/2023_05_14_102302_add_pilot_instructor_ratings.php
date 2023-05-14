<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_qualification')->insert(
            [
                'code' => 'FI',
                'type' => 'pilot',
                'name_small' => 'FI',
                'name_long' => 'Flight Instructor',
                'name_grp' => 'Flight Instructor',
                'vatsim' => 31,
            ]
        );

        DB::table('mship_qualification')->insert(
            [
                'code' => 'FE',
                'type' => 'pilot',
                'name_small' => 'FE',
                'name_long' => 'Flight Examiner',
                'name_grp' => 'Flight Examiner',
                'vatsim' => 63,
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('mship_qualification')->where('code', 'FI')->delete();
        DB::table('mship_qualification')->where('code', 'FE')->delete();
    }
};
