<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAircraftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('smartcars_aircraft', function (Blueprint $table) {
            $table->increments('id');
            $table->string('icao', 4);
            $table->string('name', 12);
            $table->string('fullname', 50);
            $table->string('registration', 5);
            $table->integer('range_nm');
            $table->integer('weight_kg');
            $table->integer('cruise_altitude');
            $table->integer('max_passengers');
            $table->integer('max_cargo_kg');
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
        Schema::dropIfExists('smartcars_aircraft');
    }
}
