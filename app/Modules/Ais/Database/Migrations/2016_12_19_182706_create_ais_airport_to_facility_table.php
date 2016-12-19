<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAisAirportToFacilityTable extends Migration
{
    public function up()
    {
        Schema::create('ais_airport_to_facility', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('airport_id')->unsigned();
            $table->integer('facility_id')->unsigned();
            $table->smallInteger('top_down_order')->unsigned();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('ais_airport_to_facility');
    }
}
