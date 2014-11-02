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
        Schema::table("mship_flag_type", function($table) {
            $table->integer("rules_required")->after("auto_close_time");
        });
        Schema::create("mship_flag_rule", function($table) {
            $table->increments("flag_rule_id")->unsigned()->primary();
            $table->integer("flag_type_id");
            $table->enum("type", array("open", "close"));
            $table->text("criterion");
            $table->text("operation");
            $table->text("value");
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
        Schema::table("mship_flag_type", function($table){
                $table->dropColumn("rules_required");
            });
        Schema::dropIfExists("mship_flag_rule");
    }

}
