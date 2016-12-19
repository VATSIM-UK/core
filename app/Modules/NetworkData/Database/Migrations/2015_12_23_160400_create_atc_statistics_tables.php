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
        // This check is required as this table was accidentally added to a very early migration.
        // It's now in use on production environments.
        // Old migration updated, so that the modules make sense on their own.

        if(!Schema::hasTable("statistic_atc")){
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
        } else {
            Schema::rename("statistic_atc", "networkdata_atc");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('networkdata_atc');
    }
}
