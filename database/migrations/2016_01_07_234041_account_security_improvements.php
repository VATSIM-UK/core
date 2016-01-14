<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AccountSecurityImprovements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // set expires_at nullable
        DB::statement('ALTER TABLE mship_account_security MODIFY expires_at TIMESTAMP NULL DEFAULT NULL;');
        DB::update('UPDATE mship_account_security SET expires_at = NULL WHERE expires_at = \'0000-00-00 00:00:00\'');

        // lengthen value
        Schema::table('mship_account_security', function ($table) {
            $table->string('value', 60)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // set expires_at back to not nullable
        DB::update('UPDATE mship_account_security SET expires_at = \'0000-00-00 00:00:00\' WHERE expires_at IS NULL');
        DB::statement('ALTER TABLE mship_account_security MODIFY expires_at TIMESTAMP NOT NULL DEFAULT "0000-00-00 00:00:00";');

        // value stays extended
        // do nothing
    }
}
