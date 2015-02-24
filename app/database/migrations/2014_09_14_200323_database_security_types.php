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

            DB::table("mship_security")->insert(array(
                ["name" => "Standard Member Security", "alpha" => 3, "numeric" => 1, "symbols" => 0, "length" => 4, "expiry" => 0, "optional" => 1, "default" => 1, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
                ["name" => "Fixed: Level 1", "alpha" => 3, "numeric" => 1, "symbols" => 0, "length" => 4, "expiry" => 45, "optional" => 0, "default" => 0, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
                ["name" => "Fixed: Level 2", "alpha" => 4, "numeric" => 2, "symbols" => 0, "length" => 6, "expiry" => 35, "optional" => 0, "default" => 0, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
                ["name" => "Fixed: Level 3", "alpha" => 5, "numeric" => 2, "symbols" => 1, "length" => 8, "expiry" => 25, "optional" => 0, "default" => 0, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
                ["name" => "Fixed: Level 4", "alpha" => 6, "numeric" => 2, "symbols" => 1, "length" => 10, "expiry" => 15, "optional" => 0, "default" => 0, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ));

            Schema::table("mship_account_security", function($table){
                $table->integer("security_id")->after("type");
            });
            DB::statement("UPDATE mship_account_security SET security_id = type");
            Schema::table("mship_account_security", function($table){
                $table->dropColumn("type");
            });

            DB::table("mship_account_security")->insert(array(
                ["section" => "mship", "area" => "account", "action" => "impersonate", "entry" => "{owner} impersonated {extra} and logged into their basic user account.  A reason was given.", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ));
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
