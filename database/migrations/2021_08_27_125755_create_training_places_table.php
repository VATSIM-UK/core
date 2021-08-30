<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainingPlacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_places', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('training_position_id');
            $table->unsignedInteger('account_id');
            $table->uuid('offer_id');
            $table->timestamp('accepted_at');
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
        Schema::dropIfExists('training_places');
    }
}
