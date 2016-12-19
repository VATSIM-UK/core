<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAisFacilityPositionTable extends Migration
{
    public function up()
    {
        Schema::create('ais_facility_position', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('facility_id')->unsigned();
            $table->string('callsign', 10);
            $table->decimal('frequency', 6, 3);
            $table->smallInteger('logon_order')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::drop('ais_facility_position');
    }
}
