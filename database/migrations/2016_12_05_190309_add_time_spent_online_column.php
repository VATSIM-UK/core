<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class AddTimeSpentOnlineColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('networkdata_atc', function ($table) {
            $table->integer('minutes_online')->after('disconnected_at')->usigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('networkdata_atc', function ($table) {
            $table->dropColumn('minutes_online');
        });
    }
}
