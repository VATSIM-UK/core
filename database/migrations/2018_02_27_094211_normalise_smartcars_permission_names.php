<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NormaliseSmartcarsPermissionNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_permission')
            ->where('name', 'smartcars')
            ->orWhere('name', 'LIKE', 'smartcars/%')
            ->update([
                'name' => DB::raw("CONCAT('adm/', name)"),
                'display_name' => DB::raw("CONCAT('Admin / ', display_name)")
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // do not reverse
    }
}
