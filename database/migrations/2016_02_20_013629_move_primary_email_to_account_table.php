<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MovePrimaryEmailToAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("mship_account", function(Blueprint $table){
            $table->string("email", 200)->after("name_last")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("mship_account", function(Blueprint $table){
            $table->dropColumn("email");
        });
    }
}
