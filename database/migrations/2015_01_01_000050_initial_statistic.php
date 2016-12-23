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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statistic');
    }
}
