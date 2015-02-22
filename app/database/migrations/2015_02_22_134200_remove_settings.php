<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveSettings extends Migration {

    /**
     * Run the migrations.
     * @return void
     */
    public function up() {
        Schema::drop('mship_account_role');
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down() {
        Schema::create("sys_setting", function($table) {
            $table->increments("setting_id")->unsigned();
            $table->string("name", 50);
            $table->text("help_text");
            $table->string("group", 40);
            $table->string("area", 25);
            $table->string("section", 25);
            $table->string("key", 25);
            $table->enum("type", array("string", "int", "double", "bool"));
            $table->text("value");
            $table->timestamps();
            $table->softDeletes();
        });
    }

}
