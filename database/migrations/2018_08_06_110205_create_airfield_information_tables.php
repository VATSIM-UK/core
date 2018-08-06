<?php

    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

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
                $table->dropColumn('ident');
                $table->string('icao', 4);
                $table->string('iata', 3);
                $table->string('name');
                $table->string('fir', 4);
                $table->text('description')->nullable();
                $table->text('departure_procedures')->nullable();
                $table->text('arrival_procedures')->nullable();
                $table->text('vfr_procedures')->nullable();
                $table->text('other_information')->nullable();
            });

            Schema::create('airport_navaids', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('airfield_id');
                $table->unsignedSmallInteger('type');
                $table->string('name')->nullable();
                $table->string('heading', 3)->nullable();
                $table->string('ident', 5);
                $table->decimal('frequency', 6, 3);
                $table->unsignedSmallInteger('frequency_band');
                $table->string('remarks')->nullable();
            });

            Schema::create('airport_runways', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('airfield_id');
                $table->string('ident', 3);
                $table->string('heading', 3);
                $table->unsignedInteger('width');
                $table->unsignedInteger('length');
                $table->unsignedSmallInteger('surface_type');
            });

            Schema::create('airport_procedures', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('airfield_id');
                $table->unsignedInteger('runway_id')->nullable();
                $table->unsignedSmallInteger('procedure_type');
                $table->string('ident');
                $table->string('initial_fix')->nullable();
                $table->integer('initial_altitude')->nullable();
                $table->integer('final_altitude')->nullable();
                $table->string('remarks')->nullable();
            });

            Schema::create('airport_stations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('airport_id')->unsigned();
                $table->integer('station_id')->unsigned();
            });

            Schema::create('stations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('callsign', 10);
                $table->string('name');
                $table->decimal('frequency', 6, 3);
                $table->unsignedSmallInteger('type');
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
                $table->string('ident', 15)->nullable()->unique();
                $table->dropColumn('icao');
                $table->dropColumn('iata');
                $table->dropColumn('name');
                $table->dropColumn('fir');
                $table->dropColumn('description');
                $table->dropColumn('departure_procedures');
                $table->dropColumn('arrival_procedures');
                $table->dropColumn('vfr_procedures');
                $table->dropColumn('other_information');
            });
            Schema::dropIfExists('airport_navaids');
            Schema::dropIfExists('airport_runways');
            Schema::dropIfExists('airport_procedures');
            Schema::dropIfExists('airport_stations');
            Schema::dropIfExists('stations');
        }
    }
