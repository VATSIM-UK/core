<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

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
