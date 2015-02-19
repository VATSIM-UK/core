<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DataChangesTracking extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create("sys_data_change", function($table) {
            $table->bigIncrements("data_change_id")->unsigned();
            $table->morphs("model");
            $table->string("data_key", 100);
            $table->text("data_old");
            $table->text("data_new");
            $table->boolean("automatic")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists("sys_data_change");
    }

}
