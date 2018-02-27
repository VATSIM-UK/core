<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SmartcarsForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('smartcars_bid', function (Blueprint $table) {
            $table->foreign('flight_id')->references('id')->on('smartcars_flight');
            $table->foreign('account_id')->references('id')->on('mship_account');
        });

        Schema::table('smartcars_flight', function (Blueprint $table) {
            $table->unsignedInteger('departure_id')->change();
            $table->unsignedInteger('arrival_id')->change();
            $table->unsignedInteger('aircraft_id')->change();

            $table->foreign('departure_id')->references('id')->on('smartcars_airport');
            $table->foreign('arrival_id')->references('id')->on('smartcars_airport');
            $table->foreign('aircraft_id')->references('id')->on('smartcars_aircraft');
        });

        Schema::table('smartcars_flight_criteria', function (Blueprint $table) {
            $table->foreign('flight_id')->references('id')->on('smartcars_flight');
        });

        Schema::table('smartcars_pirep', function (Blueprint $table) {
            $table->foreign('bid_id')->references('id')->on('smartcars_bid');
            $table->foreign('aircraft_id')->references('id')->on('smartcars_aircraft');
        });

        Schema::table('smartcars_posrep', function (Blueprint $table) {
            $table->foreign('bid_id')->references('id')->on('smartcars_bid');
            $table->foreign('aircraft_id')->references('id')->on('smartcars_aircraft');
        });

        Schema::table('smartcars_session', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('mship_account');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('smartcars_bid', function (Blueprint $table) {
            $table->dropForeign(['flight_id']);
            $table->dropForeign(['account_id']);
        });

        Schema::table('smartcars_flight', function (Blueprint $table) {
            $table->dropForeign(['departure_id']);
            $table->dropForeign(['arrival_id']);
            $table->dropForeign(['aircraft_id']);
        });

        Schema::table('smartcars_flight', function (Blueprint $table) {
            $table->integer('departure_id')->change();
            $table->integer('arrival_id')->change();
            $table->integer('aircraft_id')->change();
        });

        Schema::table('smartcars_flight_criteria', function (Blueprint $table) {
            $table->dropForeign(['flight_id']);
        });

        Schema::table('smartcars_pirep', function (Blueprint $table) {
            $table->dropForeign(['bid_id']);
            $table->dropForeign(['aircraft_id']);
        });

        Schema::table('smartcars_posrep', function (Blueprint $table) {
            $table->dropForeign(['bid_id']);
            $table->dropForeign(['aircraft_id']);
        });

        Schema::table('smartcars_session', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });
    }
}
