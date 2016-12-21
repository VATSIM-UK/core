<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class StoreFrequencyOfAtcSession extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('networkdata_atc', function ($table) {
            $table->double('frequency', 6, 3)->after('callsign')->unsigned()->nullable();
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
            $table->dropColumn('frequency');
        });
    }
}
