<?php

use Illuminate\Database\Migrations\Migration;

class CreateAtcStatisticsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('networkdata_atc', function ($table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->string('callsign', 10);
            $table->smallInteger('qualification_id')->unsigned();
            $table->tinyInteger('facility_type')->unsigned();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('disconnected_at')->nullable();
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
        Schema::drop('networkdata_atc');
    }
}
