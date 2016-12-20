<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAisFacilityToAirportTable extends Migration {

	public function up()
	{
		Schema::create('ais_facility_to_airport', function(Blueprint $table) {
			$table->increments('id');
			$table->bigInteger('airport_id')->unsigned();
			$table->integer('facility_id')->unsigned();
			$table->smallInteger('top_down_order')->unsigned();
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('ais_facility_to_airport');
	}
}