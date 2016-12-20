<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAisFirTable extends Migration {

	public function up()
	{
		Schema::create('ais_fir', function(Blueprint $table) {
			$table->increments('id');
			$table->string('icao', 4);
			$table->string('name', 50);
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::drop('ais_fir');
	}
}