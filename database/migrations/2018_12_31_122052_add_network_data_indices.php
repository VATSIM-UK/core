<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNetworkDataIndices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('networkdata_atc', function (Blueprint $table) {
            $table->index('callsign');
        });

        Schema::table('networkdata_pilots', function (Blueprint $table) {
            $table->index(['departure_airport', 'arrival_airport']);
            $table->index('departure_airport');
            $table->index('arrival_airport');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('networkdata_atc', function (Blueprint $table) {
            $table->dropIndex('networkdata_atc_callsign_index');
        });

        Schema::table('networkdata_pilots', function (Blueprint $table) {
            $table->dropIndex('networkdata_pilots_departure_airport_arrival_airport_index');
            $table->dropIndex('networkdata_pilots_departure_airport_index');
            $table->dropIndex('networkdata_pilots_arrival_airport_index');
        });
    }
}
