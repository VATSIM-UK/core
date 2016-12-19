<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAisAirportTable extends Migration {

	public function up()
	{
		Schema::create('ais_airport', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('sector_id')->unsigned();
			$table->string('icao', 4)->unique()->nullable();
			$table->string('iata', 3)->unique()->nullable();
			$table->string('name', 50);
			$table->decimal('latitude', 8,6);
			$table->decimal('longitude', 8,6);
			$table->boolean('display')->default(0);
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::drop('ais_airport');
	}
}