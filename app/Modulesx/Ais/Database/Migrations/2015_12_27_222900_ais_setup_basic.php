<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AisSetupBasic extends Migration {

    public function up(){
        Schema::create("ais_aerodrome", function($table){
            $table->increments("id");
            $table->integer("sector_id")->unsigned();
            $table->string("icao", 4)->unique();
            $table->string("iata", 3)->unique();
            $table->string("name", 75);
            $table->decimal("latitude", 8, 6)->default(0);
            $table->decimal("longitude", 8, 6)->default(0);
            $table->boolean("display")->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign("sector_id")->references("id")->on("ais_fir_sector");
        });

        Schema::create("ais_facility", function($table){
            $table->increments("id");
            $table->string("name", 60);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create("ais_facility_position", function($table){
            $table->increments("id");
            $table->integer("facility_id")->unsigned();
            $table->string("callsign", 10)->unique();
            $table->decimal("frequency", 6, 3)->default(0);
            $table->smallInteger("logon_order")->unsigned()->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign("facility_id")->references("id")->on("ais_facility");
        });

        Schema::create("ais_aerodrome_facility", function($table){
            $table->increments("id");
            $table->integer("aerodrome_id");
            $table->integer("facility_id");
            $table->smallInteger("top_down_order")->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(["aerodrome_id", "facility_id"]);

            $table->foreign("aerodrome_id")->references("id")->on("ais_aerodrome");
            $table->foreign("facility_id")->references("id")->on("ais_facility");
        });

        Schema::create("ais_fir_sector", function($table){
            $table->increments("id");
            $table->integer("fir_id")->unsigned();
            $table->integer("covered_by")->unsigned();
            $table->string("name", 35);
            $table->string("callsign_default", 10)->unique();
            $table->string("callsign_rule", 100);
            $table->decimal("frequency", 6, 3)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign("fir_id")->references("id")->on("ais_fir");
            $table->foreign("covered_by")->references("id")->on("ais_fir_sector");
        });

        Schema::create("ais_fir", function($table){
            $table->increments("id");
            $table->string("icao", 4)->unique();
            $table->string("name", 50);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(){
        Schema::dropIfExists("ais_aerodrome");
        Schema::dropIfExists("ais_facility");
        Schema::dropIfExists("ais_facility_position");
        Schema::dropIfExists("ais_aerodrome_facility");
        Schema::dropIfExists("ais_fir_sector");
        Schema::dropIfExists("ais_fir");
    }

}