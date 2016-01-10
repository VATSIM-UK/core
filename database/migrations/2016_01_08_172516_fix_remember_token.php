<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixRememberToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE mship_account MODIFY remember_token VARCHAR(100) NULL DEFAULT NULL;');
        DB::update('UPDATE mship_account SET remember_token = NULL WHERE remember_token = ""');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // remember_token stays nullable
        // do nothing
    }
}
