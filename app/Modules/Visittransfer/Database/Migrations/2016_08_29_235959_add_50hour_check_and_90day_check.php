<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Add50HourCheckAnd90DayCheck extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("vt_application", function (Blueprint $table) {
            $table->boolean("check_outcome_90_day")->after("should_perform_checks")->nullable()->default(NULL);
            $table->boolean("check_outcome_50_hours")->after("check_outcome_90_day")->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("vt_application", function (Blueprint $table) {
            $table->dropColumn("check_outcome_50hours");
            $table->dropColumn("check_outcome_90day");
        });
    }
}