<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MembershipAccountNotes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create("mship_note_type", function($table){
                $table->increments("note_type_id")->unsigned()->primary();
                $table->string("code", 10);
                $table->string("name", 50);
                $table->boolean("is_system");
                $table->timestamps();
                $table->softDeletes();
            });
            Schema::create("mship_account_note", function($table){
                $table->bigIncrements("account_note_id")->unsigned()->primary();
                $table->integer("account_id")->unsigned();
                $table->integer("note_type_id")->unsigned();
                $table->text("content");
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
		Schema::dropIfExists("mship_note_type");
		Schema::dropIfExists("mship_account_note");
	}

}
