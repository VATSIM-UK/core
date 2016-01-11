<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RepealBans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("mship_account_ban", function(Blueprint $table){
            $table->timestamp("repealed_at")->nullable()->after("updated_at");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mship_account_ban', function(Blueprint $table) {
            $table->dropColumn("repealed_at");
        });
    }
}
