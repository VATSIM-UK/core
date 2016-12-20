<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAisFirSectorTable extends Migration {

	public function up()
	{
		Schema::create('ais_fir_sector', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('fir_id')->unsigned();
			$table->string('name', 50);
			$table->string('callsign_default', 10);
			$table->string('callsign_rule', 100)->nullable();
			$table->decimal('frequency', 6,3);
			$table->timestamps();
			$table->integer('covered_by')->unsigned();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::drop('ais_fir_sector');
	}
}