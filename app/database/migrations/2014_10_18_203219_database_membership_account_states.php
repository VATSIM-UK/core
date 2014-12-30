<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DatabaseMembershipAccountStates extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            // Also need to add rules....
            Schema::create("mship_states", function($table){
                $table->increments("state_id");
                $table->string("code", 3)->unique();
                $table->enum("type", array("atc", "pilot", "training_atc", "training_pilot", "admin"));
                $table->string("name_small", 15);
                $table->string("name_long", 25);
                $table->string("name_grp", 40);
                $table->smallInteger("vatsim");
                $table->timestamps();
                $table->softDeletes();
                $table->unique(array("type", "vatsim"));
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists("mship_states");
	}

}
