<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveBanPeriod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("mship_account_ban", function(Blueprint $table){
            $table->dropColumn("period_amount");
            $table->dropColumn("period_unit");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("mship_account_ban", function(Blueprint $table){
            $table->smallInteger('period_amount')->unsigned()->after("reason_extra");
            $table->enum('period_unit', array('M', 'H', 'D'))->after("period_amount");
        });
    }
}
