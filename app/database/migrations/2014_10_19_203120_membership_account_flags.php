<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MembershipAccountNotes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table("mship_account_note", function($table) {
            $table->integer("account_flag_id")->unsigned()->after("note_type_id");
        });
        Schema::create("mship_flag_type", function($table) {
            $table->increments("flag_type_id")->unsigned()->primary();
            $table->string("code", 10);
            $table->string("name", 50);
            $table->boolean("is_system");
            $table->boolean("is_closable");
            $table->boolean("auto_close_time");
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create("mship_account_flag", function($table) {
            $table->bigIncrements("account_flag_id")->unsigned()->primary();
            $table->integer("flag_type_id")->unsigned();
            $table->morph("assigned_to");
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
    public function down() {
        Schema::table("mship_account_note", function($table){
                $table->dropColumn("account_flag_id");
            });
        Schema::dropIfExists("mship_flag_type");
        Schema::dropIfExists("mship_account_flag");
    }

}
