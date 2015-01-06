<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DatabaseSessions extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('sys_sessions', function($table) {
            $table->string('id')->unique();
            $table->text('payload');
            $table->integer('last_activity');
        });

        Schema::table("mship_account", function($table){
           $table->string("session_id")->after("salt");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists("sys_sessions");

        Schema::table("mship_account", function($table){
           $table->dropColumn("session_id");
        });
    }

}
