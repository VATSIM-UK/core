<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAisFirTable extends Migration
{
    public function up()
    {
        Schema::create('ais_fir', function (Blueprint $table) {
            $table->increments('id');
            $table->string('icao', 4);
            $table->string('name', 50);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::drop('ais_fir');
    }
}
