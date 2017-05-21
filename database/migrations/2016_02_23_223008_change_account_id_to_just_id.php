<?php

use Illuminate\Database\Migrations\Migration;

class ChangeAccountIdToJustId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE mship_account CHANGE account_id id INTEGER UNSIGNED');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE mship_account CHANGE id account_id INTEGER UNSIGNED');
    }
}
