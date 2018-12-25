<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DifferentiateBetweenPilotAndAtcFacilities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vt_application', function (Blueprint $table) {
            $table->enum('training_team', ['atc', 'pilot'])->after('type')->nullable();
        });

        Schema::table('vt_facility', function (Blueprint $table) {
            $table->boolean('can_visit')->after('description')->default(0);
            $table->boolean('can_transfer')->after('description')->default(0);
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
            $table->dropColumn('training_team');
        });

        Schema::table('vt_facility', function (Blueprint $table) {
            $table->dropColumn('can_visit');
            $table->dropColumn('can_transfer');
        });
    }
}
