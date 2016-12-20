<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAisFirSectorTable extends Migration
{
    public function up()
    {
        Schema::create('ais_fir_sector', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fir_id')->unsigned();
            $table->string('name', 100);
            $table->string('name_radio', 100);
            $table->string('callsign_primary', 10);
            $table->string('callsign_secondary', 100)->nullable();
            $table->decimal('frequency', 6, 3);
            $table->timestamps();
            $table->integer('covered_by')->unsigned();
            $table->softDeletes();
        });

        /*************************************
         * |         |            | LON_S_CTR
         * |         | LON_SC_CTR | LON_C_CTR
         * | LON_CTR |            |
         * |         |        LON_N_CTR
         * |         |        LON_W_CTR
         * ***********************************/

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')->where('icao', '=', 'EGTT')->first()->id,
                'name'               => 'London Bandbox',
                'name_radio'         => 'London Control',
                'callsign_primary'   => 'LON_CTR',
                'callsign_secondary' => 'LON__CTR',
                'frequency'          => '123.900',
                'covered_by'         => 0,
            ],
        ]);

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')->where('icao', '=', 'EGTT')->first()->id,
                'name'               => 'London Information',
                'name_radio'         => 'London Information',
                'callsign_primary'   => 'EGTT_I_CTR',
                'callsign_secondary' => 'EGTT_I__CTR',
                'frequency'          => '124.6500',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'LON_CTR')
                                          ->first()->id,
            ],
        ]);

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGTT')
                                          ->first()->id,
                'name'               => 'London South Central',
                'name_radio'         => 'London Control',
                'callsign_primary'   => 'LON_SC_CTR',
                'callsign_secondary' => 'LON_CS_CTR',
                'frequency'          => '132.600',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'LON_CTR')
                                          ->first()->id,
            ],
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGTT')
                                          ->first()->id,
                'nane'               => 'London North',
                'name_radio'         => 'London Control',
                'callsign_primary'   => 'LON_N_CTR',
                'callsign_secondary' => 'LON_N__CTR',
                'frequency'          => '133.700',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'LON_CTR')
                                          ->first()->id,
            ],
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGTT')
                                          ->first()->id,
                'name'               => 'London West',
                'name_radio'         => 'London Control',
                'callsign_primary'   => 'LON_W_CTR',
                'callsign_secondary' => 'LON_W__CTR',
                'frequency'          => '126.075',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'LON_CTR')
                                          ->first()->id,
            ],
        ]);

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGTT')
                                          ->first()->id,
                'name'               => 'London Worthing',
                'name_radio'         => 'London Control',
                'callsign_primary'   => 'LON_S_CTR',
                'callsign_secondary' => 'LON_S__CTR',
                'frequency'          => '129.425',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'LON_SC_CTR')
                                          ->first()->id,
            ],
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGTT')
                                          ->first()->id,
                'name'               => 'London Daventry',
                'name_radio'         => 'London Control',
                'callsign_primary'   => 'LON_C_CTR',
                'callsign_secondary' => 'LON_C__CTR',
                'frequency'          => '127.100',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'LON_SC_CTR')
                                          ->first()->id,
            ],
        ]);

        /*************************************
         * |       |        |       LON_S
         * |       | LON_S  | LTC_S* | LTC_SW
         * | LON_S |
         * |       | LON_D* | LTC_S* | LTC_SE
         * |       |        |       LON_D
         * ***********************************/

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGTT')
                                          ->first()->id,
                'name'               => 'London Dover',
                'name_radio'         => 'London Control',
                'callsign_primary'   => 'LON_D_CTR',
                'callsign_secondary' => 'LON_D__CTR',
                'frequency'          => '134.900',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'LON_S_CTR')
                                          ->first()->id,
            ],
        ]);

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGTT')
                                          ->first()->id,
                'name'               => 'London TC South',
                'name_radio'         => 'London Control',
                'callsign_primary'   => 'LTC_S_CTR',
                'callsign_secondary' => 'LTC_S__CTR',
                'frequency'          => '134.125',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'LON_D_CTR')
                                          ->first()->id,
            ],
        ]);

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGTT')
                                          ->first()->id,
                'name'               => 'London TC South West',
                'name_radio'         => 'London Control',
                'callsign_primary'   => 'LTC_SW_CTR',
                'callsign_secondary' => 'LTC_WS_CTR',
                'frequency'          => '133.175',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'LTC_S_CTR')
                                          ->first()->id,
            ],
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGTT')
                                          ->first()->id,
                'name'               => 'London TC South East',
                'name_radio'         => 'London Control',
                'callsign_primary'   => 'LTC_SE_CTR',
                'callsign_secondary' => 'LTC_ES_CTR',
                'frequency'          => '120.525',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'LTC_S_CTR')
                                          ->first()->id,
            ],
        ]);

        /*************************************
         * |       |        |       LON_C
         * |       | LON_C  | LTC_N* | LTC_NW
         * | LON_C |
         * |       | LON_E* | LTC_N* | LTC_NE
         * |       |        |       LON_E
         * ***********************************/

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGTT')
                                          ->first()->id,
                'name'               => 'London Clacton',
                'name_radio'         => 'London Control',
                'callsign_primary'   => 'LON_E_CTR',
                'callsign_secondary' => 'LON_E_CTR',
                'frequency'          => '121.225',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'LON_C_CTR')
                                          ->first()->id,
            ],
        ]);

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGTT')
                                          ->first()->id,
                'name'               => 'London TC North',
                'name_radio'         => 'London Control',
                'callsign_primary'   => 'LTC_N_CTR',
                'callsign_secondary' => 'LTC_N__CTR',
                'frequency'          => '119.775',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'LON_E_CTR')
                                          ->first()->id,
            ],
        ]);

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGTT')
                                          ->first()->id,
                'name'               => 'London TC North West',
                'name_radio'         => 'London Control',
                'callsign_primary'   => 'LTC_NW_CTR',
                'callsign_secondary' => 'LTC_WN_CTR',
                'frequency'          => '121.275',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'LTC_N_CTR')
                                          ->first()->id,
            ],
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGTT')
                                          ->first()->id,
                'name'               => 'London TC North East',
                'name_radio'         => 'London Control',
                'callsign_primary'   => 'LTC_NE_CTR',
                'callsign_secondary' => 'LTC_EN_CTR',
                'frequency'          => '118.825',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'LTC_N_CTR')
                                          ->first()->id,
            ],
        ]);

        /*************************************
         * |       |    LON_N
         * | LON_N | MAN | LTC_W
         * |       | MAN | MAN_E
         * ***********************************/

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGTT')
                                          ->first()->id,
                'name'               => 'Manchester Control',
                'name_radio'         => 'Scottish Control',
                'callsign_primary'   => 'MAN_CTR',
                'callsign_secondary' => 'MAN__CTR',
                'frequency'          => '118.775',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'LON_N_CTR')
                                          ->first()->id,
            ],
        ]);

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGTT')
                                          ->first()->id,
                'name'               => 'Manchester West',
                'name_radio'         => 'Scottish Control',
                'callsign_primary'   => 'MAN_W_CTR',
                'callsign_secondary' => 'MAN_W__CTR',
                'frequency'          => '128.050',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'MAN_CTR')
                                          ->first()->id,
            ],
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGTT')
                                          ->first()->id,
                'name'               => 'Manchester East',
                'name_radio'         => 'Scottish Control',
                'callsign_primary'   => 'MAN_E_CTR',
                'callsign_secondary' => 'MAN_E__CTR',
                'frequency'          => '113.800',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'MAN_CTR')
                                          ->first()->id,
            ],
        ]);

        /*************************************
         * | SCO_CTR
         * ***********************************/

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGPX')
                                          ->first()->id,
                'name'               => 'Scottish Bandbox',
                'name_radio'         => 'Scottish Control',
                'callsign_primary'   => 'SCO_CTR',
                'callsign_secondary' => 'SCO__CTR',
                'frequency'          => '135.525',
            ],
        ]);

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGPX')
                                          ->first()->id,
                'name'               => 'Scottish Information',
                'name_radio'         => 'Scottish Information',
                'callsign_primary'   => 'EGPX_I_CTR',
                'callsign_secondary' => 'EGPX_I__CTR',
                'frequency'          => '119.875',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'SCO_CTR')
                                          ->first()->id,
            ],
        ]);

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGPX')
                                          ->first()->id,
                'name'               => 'Scottish East',
                'name_radio'         => 'Scottish Control',
                'callsign_primary'   => 'SCO_E_CTR',
                'callsign_secondary' => 'SCO_E__CTR',
                'frequency'          => '121.325',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'SCO_CTR')
                                          ->first()->id,
            ],
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGPX')
                                          ->first()->id,
                'name'               => 'Scottish West LAG Bandbox',
                'name_radio'         => 'Scottish Control',
                'callsign_primary'   => 'SCO_WD_CTR',
                'callsign_secondary' => 'SCO_DW_CTR',
                'frequency'          => '133.200',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'SCO_CTR')
                                          ->first()->id,
            ],
        ]);

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGPX')
                                          ->first()->id,
                'name'               => 'Scottish Deancross',
                'name_radio'         => 'Scottish Control',
                'callsign_primary'   => 'SCO_D_CTR',
                'callsign_secondary' => 'SCO_D__CTR',
                'frequency'          => '135.850',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'SCO_WD_CTR')
                                          ->first()->id,
            ],
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGPX')
                                          ->first()->id,
                'name'               => 'Scottish West + Rathlin',
                'name_radio'         => 'Scottish Control',
                'callsign_primary'   => 'SCO_W_CTR',
                'callsign_secondary' => 'SCO_W__CTR',
                'frequency'          => '132.725',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'SCO_WD_CTR')
                                          ->first()->id,
            ],
        ]);

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGPX')
                                          ->first()->id,
                'name'               => 'Scottish Rathlin',
                'name_radio'         => 'Scottish Control',
                'callsign_primary'   => 'SCO_R_CTR',
                'callsign_secondary' => 'SCO_R__CTR',
                'frequency'          => '129.100',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'SCO_W_CTR')
                                          ->first()->id,
            ],
        ]);

        DB::table('ais_fir_sector')->insert([
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGPX')
                                          ->first()->id,
                'name'               => 'Scottish Low Level TMA',
                'name_radio'         => 'Scottish Control',
                'callsign_primary'   => 'STC_CTR',
                'callsign_secondary' => 'STC__CTR',
                'frequency'          => '124.825',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'SCO_D_CTR')
                                          ->first()->id,
            ],
            [
                'fir_id'             => DB::table('ais_fir')
                                          ->where('icao', '=', 'EGPX')
                                          ->first()->id,
                'name'               => 'Scottish Low Level Antrim',
                'name_radio'         => 'Scottish Control',
                'callsign_primary'   => 'STC_A_CTR',
                'callsign_secondary' => 'STC_A__CTR',
                'frequency'          => '132.725',
                'covered_by'         => DB::table('ais_fir_sector')
                                          ->where('callsign_primary', '=', 'SCO_WD_CTR')
                                          ->first()->id,
            ],
        ]);
    }

    public function down()
    {
        Schema::drop('ais_fir_sector');
    }
}
