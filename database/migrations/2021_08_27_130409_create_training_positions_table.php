<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainingPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('station_id')->nullable();
            $table->unsignedInteger('cts_position_id');
            $table->unsignedInteger('waiting_list_id');
            $table->unsignedInteger('places')->comment('Maximum number of training places available for this position.');
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
        Schema::dropIfExists('training_positions');
    }
}
