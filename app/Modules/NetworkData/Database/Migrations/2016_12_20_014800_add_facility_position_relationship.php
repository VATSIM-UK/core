<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class AddFacilityPositionRelationship extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('networkdata_atc', function ($table) {
            $table->integer('facility_position_id')->unsigned()->after('account_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('networkdata_atc', function ($table) {
            $table->dropColumn('facility_position_id');
        });
    }
}
