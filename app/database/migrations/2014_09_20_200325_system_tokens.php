<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SystemTokens extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create("sys_token", function($table){
                $table->increments("token_id")->unsigned();
                $table->morphs("related");
                $table->enum("type", array("mship_account_security_reset"));
                $table->string("code", 31);
                $table->timestamps();
                $table->timestamp("expires_at")->nullable;
                $table->timestamp("used_at")->nullable();
                $table->softDeletes();
                $table->unique("code");
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists("sys_token");
	}

}
