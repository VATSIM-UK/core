<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DatabaseQualifications extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create("mship_qualification", function($table) {
            $table->increments("qualification_id");
            $table->string("code", 3)->unique();
            $table->enum("type", array("atc", "pilot", "training_atc", "training_pilot", "admin"));
            $table->string("name_small", 15);
            $table->string("name_long", 25);
            $table->string("name_grp", 40);
            $table->smallInteger("vatsim");
            $table->timestamps();
            $table->softDeletes();
            $table->unique(array("type", "vatsim"));
        });

        DB::table("mship_qualification")->insert(array(
            ["code" => "OBS", "type" => "atc", "name_small" => "OBS", "name_long" => "Observer", "name_grp" => "Observer", "vatsim" => 1, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "S1", "type" => "atc", "name_small" => "STU", "name_long" => "Student 1", "name_grp" => "Ground Controller", "vatsim" => 2, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "S2", "type" => "atc", "name_small" => "STU2", "name_long" => "Student 2", "name_grp" => "Tower Controller", "vatsim" => 3, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "S3", "type" => "atc", "name_small" => "STU+", "name_long" => "Student 3", "name_grp" => "Approach Controller", "vatsim" => 4, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "C1", "type" => "atc", "name_small" => "CTR", "name_long" => "Controller 1", "name_grp" => "Area Controller", "vatsim" => 5, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "C3", "type" => "atc", "name_small" => "CTR+", "name_long" => "Senior Controller", "name_grp" => "Senior Controller", "vatsim" => 7, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],

            ["code" => "I1", "type" => "training_atc", "name_small" => "INS", "name_long" => "Instructor", "name_grp" => "Instructor", "vatsim" => 8, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "I3", "type" => "training_atc", "name_small" => "INS+", "name_long" => "Senior Instructor", "name_grp" => "Senior Instructor", "vatsim" => 10, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],

            ["code" => "SUP", "type" => "admin", "name_small" => "SUP", "name_long" => "Supervisor", "name_grp" => "Network Supervisor", "vatsim" => 11, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "ADM", "type" => "admin", "name_small" => "ADM", "name_long" => "Administrator", "name_grp" => "Network Administrator", "vatsim" => 12, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],

            ["code" => "P1", "type" => "pilot", "name_small" => "P1", "name_long" => "P1", "name_grp" => "Online Pilot", "vatsim" => 1, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "P2", "type" => "pilot", "name_small" => "P2", "name_long" => "P2", "name_grp" => "Flight Fundamentals", "vatsim" => 2, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "P3", "type" => "pilot", "name_small" => "P3", "name_long" => "P3", "name_grp" => "VFR Pilot", "vatsim" => 4, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "P3", "type" => "pilot", "name_small" => "P4", "name_long" => "P4", "name_grp" => "IFR Pilot", "vatsim" => 8, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "P3", "type" => "pilot", "name_small" => "P5", "name_long" => "P5", "name_grp" => "Advanced IFR Pilot", "vatsim" => 16, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "P3", "type" => "pilot", "name_small" => "P6", "name_long" => "P6", "name_grp" => "P6", "vatsim" => 32, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "P3", "type" => "pilot", "name_small" => "P7", "name_long" => "P7", "name_grp" => "P7", "vatsim" => 64, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "P3", "type" => "pilot", "name_small" => "P8", "name_long" => "P8", "name_grp" => "P8", "vatsim" => 128, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "P3", "type" => "pilot", "name_small" => "P9", "name_long" => "P9", "name_grp" => "Pilot Flight Instructor", "vatsim" => 256, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
        ));

        Schema::table('mship_account_qualification', function($table) {
            $table->dropColumn('type');
            $table->dropColumn('value');
            $table->integer('qualification_id')->unsigned()->after('account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists("mship_qualification");
        Schema::table('mship_account_qualification', function($table) {
            $table->enum("type", array("ATC", "Pilot", "Training_ATC", "Training_Pilot", "Admin"))->after('account_id');
            $table->smallInteger("value")->unsigned()->after('type');
            $table->dropColumn("qualification_id");
        });
    }

}
