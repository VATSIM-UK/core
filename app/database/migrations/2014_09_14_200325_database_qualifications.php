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
