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
        DB::statement('ALTER TABLE mship_qualification MODIFY COLUMN `name_long` varchar(255)');
        DB::statement('ALTER TABLE mship_qualification MODIFY COLUMN `code` varchar(10)');

        // updates from here: https://tech.vatsim.net/blog/vatsim_services/new_pilot_ratings
        DB::table('mship_qualification')->where('code', 'P0')
            ->update(
                [
                    'name_long' => 'No Pilot Rating',
                ]
            );

        DB::table('mship_qualification')->where('code', 'P1')
            ->update(
                [
                    'vatsim' => 1,
                    'name_small' => 'PPL',
                    'name_long' => 'Private Pilot License',
                    'code' => 'PPL',
                ]
            );

        DB::table('mship_qualification')->where('code', 'P2')
            ->update(
                [
                    'vatsim' => 3,
                    'name_small' => 'IR',
                    'name_long' => 'Instrument Rating',
                    'code' => 'IR',
                ]
            );

        DB::table('mship_qualification')->where('code', 'P3')
            ->update(
                [
                    'vatsim' => 7,
                    'name_small' => 'CMEL',
                    'name_long' => 'Commercial Multi-Engine License',
                    'code' => 'CMEL',
                ]
            );

        DB::table('mship_qualification')->where('code', 'P4')
            ->update(
                [
                    'vatsim' => 15,
                    'name_small' => 'ATPL',
                    'name_long' => 'Airline Transport Pilot License',
                    'code' => 'ATPL',
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
