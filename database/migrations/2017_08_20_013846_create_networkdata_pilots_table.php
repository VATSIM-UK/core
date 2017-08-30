<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNetworkdataPilotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('networkdata_pilots', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id');
            $table->string('callsign', 10);
            $table->string('flight_type', 1);
            $table->string('departure_airport');
            $table->string('arrival_airport');
            $table->string('alternative_airport');
            $table->string('aircraft');
            $table->string('cruise_altitude');
            $table->string('cruise_tas');
            $table->text('route');
            $table->text('remarks');
            $table->double('current_latitude', 12, 8)->nullable();
            $table->double('current_longitude', 12, 8)->nullable();
            $table->mediumInteger('current_altitude')->nullable();
            $table->unsignedSmallInteger('current_groundspeed')->nullable();
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('disconnected_at')->nullable();
            $table->unsignedInteger('minutes_online')->nullable();
            $table->timestamps();
        });

        Schema::table('networkdata_pilots', function (Blueprint $table) {
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
        Schema::table('networkdata_pilots', function (Blueprint $table) {
            $table->dropForeign('networkdata_pilots_account_id_foreign');
        });

        Schema::dropIfExists('networkdata_pilots');
    }
}
