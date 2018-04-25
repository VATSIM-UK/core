<?php

use Illuminate\Database\Migrations\Migration;

class AddEndorsementPermission extends Migration
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
                'name' => 'adm/atc',
                'display_name' => 'Admin / ATC',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'name' => 'adm/atc/endorsement',
                'display_name' => 'Admin / ATC / Endorsements',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
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
        DB::table('mship_permission')
            ->where('name', 'adm/atc')
            ->orWhere('name', 'adm/atc/endorsement')
            ->delete();
    }
}
