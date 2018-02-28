<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrivaccContact extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('contacts')->insert([
            ['key' => 'PRIVACC', 'name' => 'Privileged Access', 'email' => 'privileged-access@vatsim.uk'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('contacts')->where('key', 'PRIVACC')->delete();
    }
}
