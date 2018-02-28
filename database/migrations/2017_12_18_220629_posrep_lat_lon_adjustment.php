<?php

use Illuminate\Database\Migrations\Migration;

class PosrepLatLonAdjustment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE smartcars_posrep MODIFY latitude DOUBLE(12,8) NOT NULL');
        DB::statement('ALTER TABLE smartcars_posrep MODIFY longitude DOUBLE(12,8) NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE smartcars_posrep MODIFY latitude DOUBLE(8,2) NOT NULL');
        DB::statement('ALTER TABLE smartcars_posrep MODIFY longitude DOUBLE(8,2) NOT NULL');
    }
}
