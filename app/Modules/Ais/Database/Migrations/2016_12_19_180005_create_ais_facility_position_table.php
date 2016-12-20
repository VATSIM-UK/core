<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAisFacilityPositionTable extends Migration {

	public function up()
	{
		Schema::create('ais_facility_position', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('facility_id')->unsigned();
			$table->string('callsign_primary', 10);
			$table->string('callsign_secondary', 10);
			$table->string('callsign_possibilities', 50);
			$table->decimal('frequency', 6,3);
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