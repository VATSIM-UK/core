<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConnectColumnsToMshipAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mship_account', function (Blueprint $table) {
            $table->text('vatsim_access_token')->after('remember_token')->nullable();
            $table->text('vatsim_refresh_token')->after('vatsim_access_token')->nullable();
            $table->unsignedBigInteger('vatsim_token_expires')->after('vatsim_refresh_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mship_account', function (Blueprint $table) {
            $table->dropColumn('vatsim_access_token');
            $table->dropColumn('vatsim_refresh_token');
            $table->dropColumn('vatsim_token_expires');
        });
    }
}
