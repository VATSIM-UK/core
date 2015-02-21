<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MembershipNotesInitial extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::create("mship_note_type", function($table){
                    $table->increments("note_type_id")->unsigned();
                    $table->string("name", 80);
                    $table->boolean("is_available")->default(1);
                    $table->boolean("is_system")->default(0);
                    $table->enum("colour_code", array("default", "info", "success", "danger", "warning"))->default("info");
                    $table->timestamps();
                    $table->softDeletes();
                });

                DB::table("mship_note_type")->insert(array(
                    ["name" => "System Generated", "is_system" => 1, "colour_code" => "default", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
                    ["name" => "General", "is_available" => 1, "colour_code" => "info", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
                ));

                Schema::create("mship_account_note", function($table){
                    $table->bigIncrements("account_note_id")->unsigned();
                    $table->integer("note_type_id")->unsigned();
                    $table->integer("account_id")->unsigned();
                    $table->integer("writer_id")->unsigned();
                    $table->text("content");
                    $table->timestamps();
                    $table->softDeletes();

                    $table->foreign('account_id')->references('account_id')->on('mship_account')->onUpdate('cascade')->onDelete('cascade');
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
		Schema::dropIfExists("mship_account_note");
		Schema::dropIfExists("mship_note_type");
	}

}
