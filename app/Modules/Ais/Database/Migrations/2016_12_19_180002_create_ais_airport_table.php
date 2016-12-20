<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAisAirportTable extends Migration
{

    public function up()
    {
        Schema::create('ais_airport', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('sector_id')->unsigned()->nullable();
            $table->string('icao', 4)->unique();
            $table->string('iata', 3)->unique()->nullable();
            $table->string('name', 100);
            $table->decimal('latitude', 8, 6);
            $table->decimal('longitude', 8, 6);
            $table->integer("elevation");
            $table->string("continent", 2)->nullable();
            $table->string("country", 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::drop('ais_airport');
    }
}