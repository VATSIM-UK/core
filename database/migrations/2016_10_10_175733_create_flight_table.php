<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlightTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('smartcars_flight', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 3);
            $table->string('flightnum', 10);
            $table->integer('departure_id');
            $table->integer('arrival_id');
            $table->text('route');
            $table->text('route_details');
            $table->integer('aircraft_id');
            $table->integer('cruise_altitude')->default(0);
            $table->float('distance')->default(0);
            $table->float('flight_time')->default(0);
            $table->text('notes');
            $table->boolean('enabled')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('smartcars_flight');
    }
}
