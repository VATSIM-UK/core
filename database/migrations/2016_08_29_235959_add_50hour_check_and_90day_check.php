<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Add50HourCheckAnd90DayCheck extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vt_application', function (Blueprint $table) {
            $table->boolean('check_outcome_90_day')->after('should_perform_checks')->nullable()->default(null);
            $table->boolean('check_outcome_50_hours')->after('check_outcome_90_day')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vt_application', function (Blueprint $table) {
            $table->dropColumn('check_outcome_50_hours');
            $table->dropColumn('check_outcome_90_day');
        });
    }
}
