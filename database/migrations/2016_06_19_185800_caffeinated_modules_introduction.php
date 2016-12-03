<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

class CaffeinatedModulesIntroduction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_permission')->insert([
            ['name' => 'adm/system/module', 'display_name' => 'Admin / System / Modules', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/system/module/*/enable', 'display_name' => 'Admin / System / Modules / Enable', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/system/module/*/disable', 'display_name' => 'Admin / System / Modules / Disable', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
