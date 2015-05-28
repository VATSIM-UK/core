<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpgradeV2109V2110 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table("sys_token", function($table){
            $table->string("type_new", 75)->after("type");
        });

        DB::table("sys_token")
            -> update(array("type_new" => DB::raw("`type`")));

        Schema::table("sys_token", function($table){
            $table->dropColumn("type");
            $table->renameColumn("type_new", "type");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        // We can't undo the token changes, as it was "previously" an enum.
    }
}
