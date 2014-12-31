<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExtraVatsimMShipData extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table("mship_account", function($table){
           $table->timestamp("joined_at")->after("is_invisible");
           $table->enum("experience", array("N", "A", "P", "B"))->after("gender");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table("mship_account", function($table){
           $table->dropColumn("joined_at");
           $table->dropColumn("experience");
        });
    }

}
