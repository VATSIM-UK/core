<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainingPlaceOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_place_offers', function (Blueprint $table) {
            $table->uuid('offer_id');
            $table->primary('offer_id');
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('offered_by');
            $table->unsignedInteger('training_position_id');
            $table->timestamp('expires_at');
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_place_offers');
    }
}
