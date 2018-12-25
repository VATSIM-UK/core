<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmartcarsFlightCriteria extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('smartcars_flight_criteria', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('flight_id');
            $table->unsignedInteger('order');
            $table->double('p1_latitude', 12, 8)->nullable();
            $table->double('p1_longitude', 12, 8)->nullable();
            $table->double('p2_latitude', 12, 8)->nullable();
            $table->double('p2_longitude', 12, 8)->nullable();
            $table->double('p3_latitude', 12, 8)->nullable();
            $table->double('p3_longitude', 12, 8)->nullable();
            $table->double('p4_latitude', 12, 8)->nullable();
            $table->double('p4_longitude', 12, 8)->nullable();
            $table->integer('min_altitude')->nullable();
            $table->integer('max_altitude')->nullable();
            $table->smallInteger('min_groundspeed')->nullable();
            $table->smallInteger('max_groundspeed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('smartcars_flight_criteria');
    }
}
