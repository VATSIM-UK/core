<?php

use Illuminate\Database\Schema\Blueprint;
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
        //        Schema::table('teamspeak_ban', function(Blueprint $table) {
//            $table->dropForeign("teamspeak_ban_account_id_foreign");
//            $table->dropForeign("teamspeak_ban_authorised_by_foreign");
//        });
//
//        Schema::table('teamspeak_registration', function(Blueprint $table) {
//            $table->dropForeign("teamspeak_registration_account_id_foreign");
//        });
//
//        Schema::table('staff_account_position', function (Blueprint $table) {
//            $table->dropForeign("staff_account_position_account_id_foreign");
//        });

        DB::statement('ALTER TABLE mship_account CHANGE account_id id INTEGER UNSIGNED');

//        Schema::table('teamspeak_ban', function(Blueprint $table) {
//            $table->foreign('account_id')->references('id')->on('mship_account');
//            $table->foreign('authorised_by')->references('id')->on('mship_account');
//        });
//
//        Schema::table('teamspeak_registration', function(Blueprint $table) {
//            $table->foreign('account_id')->references('id')->on('mship_account');
//        });
//
//        Schema::table('staff_account_position', function (Blueprint $table) {
//            $table->foreign('account_id')->references('id')->on('mship_account');
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //        Schema::table('teamspeak_ban', function(Blueprint $table) {
//            $table->dropForeign("teamspeak_ban_account_id_foreign");
//            $table->dropForeign("teamspeak_ban_authorised_by_foreign");
//        });
//
//        Schema::table('teamspeak_registration', function(Blueprint $table) {
//            $table->dropForeign("teamspeak_registration_account_id_foreign");
//        });
//
//        Schema::table('staff_account_position', function (Blueprint $table) {
//            $table->dropForeign("staff_account_position_account_id_foreign");
//        });

        DB::statement('ALTER TABLE mship_account CHANGE id account_id INTEGER UNSIGNED');

//        Schema::table('teamspeak_alias', function(Blueprint $table) {
//            $table->foreign('account_id')->references('account_id')->on('mship_account');
//        });
//
//        Schema::table('teamspeak_ban', function(Blueprint $table) {
//            $table->foreign('account_id')->references('account_id')->on('mship_account');
//            $table->foreign('authorised_by')->references('account_id')->on('mship_account');
//        });
//
//        Schema::table('teamspeak_registration', function(Blueprint $table) {
//            $table->foreign('account_id')->references('account_id')->on('mship_account');
//        });
//
//        Schema::table('staff_account_position', function (Blueprint $table) {
//            $table->foreign('account_id')->references('account_id')->on('mship_account');
//        });
    }
}
