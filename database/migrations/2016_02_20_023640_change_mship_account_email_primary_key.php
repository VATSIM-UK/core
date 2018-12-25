<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeMshipAccountEmailPrimaryKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mship_account_email', function (Blueprint $table) {
            $table->renameColumn('account_email_id', 'id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mship_account_email', function (Blueprint $table) {
            $table->renameColumn('id', 'account_email_id');
        });
    }
}
