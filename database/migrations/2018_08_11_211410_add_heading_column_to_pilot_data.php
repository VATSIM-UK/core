<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHeadingColumnToPilotData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('networkdata_pilots', function (Blueprint $table) {
            $table->unsignedSmallInteger('current_heading')->nullable()->after('current_groundspeed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('networkdata_pilots', function (Blueprint $table) {
            $table->dropColumn('current_heading');
        });
    }
}
