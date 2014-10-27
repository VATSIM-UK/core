<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InitialMembership extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::create("mship_account", function($table){
                    $table->engine = 'MyISAM';
                    $table->integer("account_id")->unsigned()->primary();
                    $table->string("name_first", 50);
                    $table->string("name_last", 50);
                    $table->string("salt", 20);
                    $table->timestamp("last_login")->nullable();
                    $table->bigInteger("last_login_ip")->unsigned();
                    $table->enum("gender", array("M", "F"))->nullable();
                    $table->smallInteger("age")->unsigned();
                    $table->string("template", 10);
                    $table->smallInteger("status")->unsigned();
                    $table->timestamps();
                    $table->timestamp("cert_checked_at")->nullable();
                    $table->softDeletes();
                });

                Schema::create("mship_account_email", function($table){
                    $table->engine = 'MyISAM';
                    $table->bigIncrements("account_email_id")->unsigned()->primary();
                    $table->integer("account_id")->unsigned();
                    $table->string("email", 80);
                    $table->boolean("is_primary")->default(0);
                    $table->timestamp("verified")->nullable();
                    $table->timestamps();
                    $table->softDeletes();
                });

                Schema::create("mship_account_qualification", function($table){
                    $table->engine = 'MyISAM';
                    $table->bigIncrements("account_qualification_id")->unsigned()->primary();
                    $table->integer("account_id")->unsigned();
                    $table->enum("type", array("ATC", "Pilot", "Training_ATC", "Training_Pilot", "Admin"));
                    $table->smallInteger("value");
                    $table->timestamps();
                    $table->softDeletes();
                });

                Schema::create("mship_account_security", function($table){
                    $table->engine = 'MyISAM';
                    $table->bigIncrements("account_security_id")->unsigned()->primary();
                    $table->integer("account_id")->unsigned();
                    $table->smallInteger("type");
                    $table->string("value", 40);
                    $table->timestamps();
                    $table->timestamp("expires_at");
                    $table->softDeletes();
                });

                Schema::create("mship_account_state", function($table){
                    $table->engine = 'MyISAM';
                    $table->bigIncrements("account_state_id")->unsigned()->primary();
                    $table->integer("account_id")->unsigned();
                    $table->tinyInteger("state");
                    $table->timestamps();
                    $table->softDeletes();
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            // Initial creation - drop all tables on rollback.
		Schema::dropIfExists("mship_account");
		Schema::dropIfExists("mship_account_email");
		Schema::dropIfExists("mship_account_qualification");
		Schema::dropIfExists("mship_account_security");
		Schema::dropIfExists("mship_account_state");
	}

}
