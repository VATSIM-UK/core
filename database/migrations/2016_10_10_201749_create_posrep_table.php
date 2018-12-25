<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePosrepTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('smartcars_posrep', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bid_id')->unsigned();
            $table->integer('aircraft_id')->unsigned();
            $table->text('route');
            $table->integer('altitude');
            $table->smallInteger('heading_mag');
            $table->smallInteger('heading_true');
            $table->float('latitude');
            $table->float('longitude');
            $table->smallInteger('groundspeed');
            $table->integer('distance_remaining');
            $table->smallInteger('phase');
            $table->time('time_departure');
            $table->time('time_remaining');
            $table->time('time_arrival');
            $table->string('network', 30);
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
        Schema::dropIfExists('smartcars_posrep');
    }
}
