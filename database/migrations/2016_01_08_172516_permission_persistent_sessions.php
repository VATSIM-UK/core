<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PermissionPersistentSessions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_permission')->insert([
            [
                'name' => 'session/persistent',
                'display_name' => 'Session / Persistent Sessions',
                'created_at' => DB::raw('NOW()'),
                'updated_at' => DB::raw('NOW()'),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('mship_permission')->where('name', 'session/persistent')->first()->delete();
    }
}
