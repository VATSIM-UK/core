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
        // add pilot_military to the enum column
        DB::statement("ALTER TABLE mship_qualification MODIFY COLUMN `type` ENUM('pilot', 'pilot_military', 'atc', 'training_atc', 'training_pilot', 'admin') NOT NULL");

        DB::table('mship_qualification')->insert(
            [
                [
                    'code' => 'M0',
                    'type' => 'pilot_military',
                    'name_small' => 'M0',
                    'name_long' => 'M0',
                    'name_grp' => 'No military pilot rating',
                    'vatsim' => 0,
                ],
                [
                    'code' => 'M1',
                    'type' => 'pilot_military',
                    'name_small' => 'M1',
                    'name_long' => 'M1',
                    'name_grp' => 'Military Pilot License',
                    'vatsim' => 1,
                ],
                [
                    'code' => 'M2',
                    'type' => 'pilot_military',
                    'name_small' => 'M2',
                    'name_long' => 'M2',
                    'name_grp' => 'Military Instrument Rating',
                    'vatsim' => 3,
                ],
                [
                    'code' => 'M3',
                    'type' => 'pilot_military',
                    'name_small' => 'M3',
                    'name_long' => 'M3',
                    'name_grp' => 'Military Multi-Engine Rating',
                    'vatsim' => 7,
                ],
                [
                    'code' => 'M4',
                    'type' => 'pilot_military',
                    'name_small' => 'M4',
                    'name_long' => 'M4',
                    'name_grp' => 'Military Mission Ready Pilot',
                    'vatsim' => 15,
                ],
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
        //
    }
};
