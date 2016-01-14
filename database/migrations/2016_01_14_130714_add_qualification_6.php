<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQualification6 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mship_qualification', function ($table) {
            $table->dropUnique('mship_qualification_code_unique');
        });

        DB::table("mship_qualification")->insert(array(
            ["code" => "C3", "type" => "atc", "name_small" => "CTR+", "name_long" => "Senior Controller", "name_grp" => "Senior Controller", "vatsim" => 6, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "I3", "type" => "training_atc", "name_small" => "INS+", "name_long" => "Senior Instructor", "name_grp" => "Senior Instructor", "vatsim" => 9, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table("mship_qualification")->where('vatsim', 6)->orWhere('vatsim', 9)->delete();

        Schema::table('mship_qualification', function ($table) {
            $table->unique('code');
        });
    }
}
