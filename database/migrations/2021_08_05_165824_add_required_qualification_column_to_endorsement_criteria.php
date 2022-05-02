<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequiredQualificationColumnToEndorsementCriteria extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('endorsement_conditions', function (Blueprint $table) {
            $table->unsignedInteger('required_qualification')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('endorsement_conditions', function (Blueprint $table) {
            $table->dropColumn('required_qualification');
        });
    }
}
