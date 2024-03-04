<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAirfieldInformationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('airports', function (Blueprint $table) {
            $table->renameColumn('ident', 'icao');
            $table->boolean('major')->default(false)->after('ident');
            $table->unsignedTinyInteger('fir_type')->nullable()->after('ident');
            $table->string('name')->nullable()->after('ident');
            $table->string('iata', 3)->nullable()->after('ident');
            $table->text('description')->nullable()->after('elevation');
            $table->text('departure_procedures')->nullable();
            $table->text('arrival_procedures')->nullable();
            $table->text('vfr_procedures')->nullable();
            $table->text('other_information')->nullable();
            $table->timestamps();
        });

        DB::table('airports')->update(['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);

        Schema::create('airport_navaids', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('airport_id');
            $table->unsignedTinyInteger('type');
            $table->string('name')->nullable();
            $table->string('heading', 3)->nullable();
            $table->string('ident', 5);
            $table->decimal('frequency', 6, 3);
            $table->unsignedSmallInteger('frequency_band');
            $table->string('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('airport_runways', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('airport_id');
            $table->string('ident', 3);
            $table->string('heading', 3);
            $table->unsignedInteger('width');
            $table->unsignedInteger('length');
            $table->unsignedSmallInteger('surface_type');
            $table->timestamps();
        });

        Schema::create('airport_procedures', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('airport_id');
            $table->unsignedInteger('runway_id')->nullable();
            $table->unsignedTinyInteger('type');
            $table->string('ident');
            $table->string('initial_fix')->nullable();
            $table->integer('initial_altitude')->nullable();
            $table->integer('final_altitude')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('airport_stations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('airport_id')->unsigned();
            $table->integer('station_id')->unsigned();
        });

        Schema::create('stations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('callsign');
            $table->string('name');
            $table->decimal('frequency', 6, 3);
            $table->unsignedTinyInteger('type');
            $table->boolean('sub_station')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('airports', function (Blueprint $table) {
            $table->renameColumn('icao', 'ident');
            $table->dropColumn('iata');
            $table->dropColumn('name');
            $table->dropColumn('fir_type');
            $table->dropColumn('major');
            $table->dropColumn('description');
            $table->dropColumn('departure_procedures');
            $table->dropColumn('arrival_procedures');
            $table->dropColumn('vfr_procedures');
            $table->dropColumn('other_information');
            $table->dropTimestamps();
        });
        Schema::dropIfExists('airport_navaids');
        Schema::dropIfExists('airport_runways');
        Schema::dropIfExists('airport_procedures');
        Schema::dropIfExists('airport_stations');
        Schema::dropIfExists('stations');
    }
}
