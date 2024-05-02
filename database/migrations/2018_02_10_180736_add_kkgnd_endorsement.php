<?php

use Illuminate\Database\Migrations\Migration;

class AddKkgndEndorsement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('endorsements')
            ->insert([
                [
                    'endorsement' => 'Gatwick S1 (DEL/GND)',
                    'required_airfields' => '["EGCC_%","EGPH_%","EGSS_%","EGGP_%"]',
                    'required_hours' => '10',
                    'hours_months' => '3',
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ],
                [
                    'endorsement' => 'Gatwick S1 (DEL/GND)',
                    'required_airfields' => '["EGPF_%","EGBB_%","EGGD_%","EGGW_%"]',
                    'required_hours' => '10',
                    'hours_months' => '3',
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ],
                [
                    'endorsement' => 'Gatwick S1 (DEL/GND)',
                    'required_airfields' => '["EGJJ_%","EGAA_%","EGNT_%","EGNX_%"]',
                    'required_hours' => '5',
                    'hours_months' => '3',
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ],
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
