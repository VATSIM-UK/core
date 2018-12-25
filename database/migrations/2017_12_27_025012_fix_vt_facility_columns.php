<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixVtFacilityColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vt_facility', function (Blueprint $table) {
            $table->integer('training_spaces')->unsigned()->default(0)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vt_facility', function (Blueprint $table) {
            $table->integer('training_spaces')->unsigned()->default(0)->change();
        });
    }
}
