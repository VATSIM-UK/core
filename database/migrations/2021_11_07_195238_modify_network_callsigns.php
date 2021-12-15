<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyNetworkCallsigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'networkdata_atc',
            function (Blueprint $table) {
                $table->string('callsign', 20)->change();
            }
        );

        Schema::table(
            'networkdata_pilots',
            function (Blueprint $table) {
                $table->string('callsign', 20)->change();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'networkdata_atc',
            function (Blueprint $table) {
                $table->string('callsign', 10)->change();
            }
        );
        Schema::table(
            'networkdata_pilots',
            function (Blueprint $table) {
                $table->string('callsign', 10)->change();
            }
        );
    }
}
