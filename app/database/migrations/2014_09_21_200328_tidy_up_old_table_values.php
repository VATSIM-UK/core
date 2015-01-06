<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TidyUpOldTableValues extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table("mship_account", function($table){
           $table->dropColumn("salt");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table("mship_account", function($table){
           $table->string("salt")->after("name_last");
        });
    }

}
