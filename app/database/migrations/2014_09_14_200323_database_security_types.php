<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DatabaseSecurityTypes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create("mship_security", function($table){
                $table->increments("security_id");
                $table->string("name", 25);
                $table->smallInteger("alpha");
                $table->smallInteger("numeric");
                $table->smallInteger("symbols");
                $table->smallInteger("length");
                $table->smallInteger("expiry");
                $table->boolean("optional");
                $table->boolean("default");
                $table->timestamps();
                $table->softDeletes();
            });
            Schema::table("mship_account_security", function($table){
                $table->integer("security_id")->after("type");
            });
            DB::statement("UPDATE mship_account_security SET security_id = type");
            Schema::table("mship_account_security", function($table){
                $table->dropColumn("type");
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            Schema::dropIfExists("mship_security");

            Schema::table("mship_account_security", function($table){
                $table->integer("type")->after("security_id");
            });
            DB::statement("UPDATE mship_account_security SET type = security_id");
            Schema::table("mship_account_security", function($table){
                $table->dropColumn("security_id");
            });
	}

}
