<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StatisticsImplementation extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create("statistic", function($table) {
            $table->bigIncrements("statistic_id")->unsigned();
            $table->date("period");
            $table->string("key", 60);
            $table->string("value");
            $table->timestamps();
            $table->softDeletes();
            $table->unique(array("period", "key"));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists("statistic");
    }

}
