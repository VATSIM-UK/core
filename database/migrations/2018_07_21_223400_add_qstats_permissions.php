<?php

use Illuminate\Database\Migrations\Migration;

class AddQStatsPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_permission')->insert([
            'name' => 'adm/ops',
            'display_name' => 'Admin / Ops',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        DB::table('mship_permission')->insert([
            'name' => 'adm/ops/qstats',
            'display_name' => 'Admin / Ops / Quarterly Stats',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('mship_permission')
            ->where('name', 'adm/ops')
            ->delete();

        DB::table('mship_permission')
            ->where('name', 'adm/ops/qstats')
            ->delete();
    }
}
