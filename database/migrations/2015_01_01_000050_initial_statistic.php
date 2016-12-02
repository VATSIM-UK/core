<?php

use Illuminate\Database\Migrations\Migration;

class InitialStatistic extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statistic', function ($table) {
            $table->bigIncrements('statistic_id')->unsigned();
            $table->date('period');
            $table->string('key', 60);
            $table->string('value');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['period', 'key']);
        });

        Schema::create('statistic_atc', function ($table) {
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
        Schema::dropIfExists('statistic');
        Schema::dropIfExists('statistic_atc');
    }
}
