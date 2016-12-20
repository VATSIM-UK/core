<?php

namespace App\Modules\Ais\Database\Seeds;

use DB;
use Illuminate\Database\Seeder;

class AisDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $man = DB::table('ais_airport')->where('icao', '=', 'EGCC')->get();

        $manTwr = DB::table('ais_facility')->insertGetId([
            'name' => 'Manchester AIR',
        ]);

        DB::table('ais_airport_to_facility')->insert([
            'airport_id'     => $man->id,
            'facility_id'    => $manTwr,
            'top_down_order' => 1,
        ]);

        DB::table('ais_facility_position')->insert([
            'facility_id'        => $manTwr,
            'callsign_primary'   => 'EGCC_N_TWR',
            'callsign_secondary' => 'EGCC_1_TWR',
            'callsign_format'    => 'EGCC\\_\\_?[NT1]\\_?\\_TWR',
            'frequency'          => '118.620',
            'logon_order'        => 1,
        ]);
    }
}
