<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key')->unique();
            $table->string('name');
            $table->string('email');
        });

        DB::table('contacts')->insert([
            ['key' => 'ATC_TRAINING', 'name' => 'ATC Training', 'email' => 'atc-team@vatsim.uk'],
            ['key' => 'PILOT_TRAINING', 'name' => 'Pilot Training', 'email' => 'pilot-team@vatsim.uk'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contacts');
    }
}
