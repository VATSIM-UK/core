<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameGatwickGndStudentsRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_role')->where(['name' => 'Gatwick Students'])->update(['name' => 'Gatwick GND Students']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('mship_role')->where(['name' => 'Gatwick GND Students'])->update(['name' => 'Gatwick Students']);
    }
}
