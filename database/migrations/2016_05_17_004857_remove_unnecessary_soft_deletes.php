<?php

use Illuminate\Database\Migrations\Migration;

class RemoveUnnecessarySoftDeletes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('statistic')->whereNotNull('deleted_at')->delete();
        Schema::table('statistic', function ($table) {
            $table->dropColumn('deleted_at');
        });

        DB::table('sso_token')->whereNotNull('deleted_at')->delete();
        Schema::table('sso_token', function ($table) {
            $table->dropColumn('deleted_at');
        });

        DB::table('sso_email')->whereNotNull('deleted_at')->delete();
        Schema::table('sso_email', function ($table) {
            $table->dropColumn('deleted_at');
        });

        DB::table('sso_account')->whereNotNull('deleted_at')->delete();
        Schema::table('sso_account', function ($table) {
            $table->dropColumn('deleted_at');
        });

        DB::table('mship_role')->whereNotNull('deleted_at')->delete();
        Schema::table('mship_role', function ($table) {
            $table->dropColumn('deleted_at');
        });

        DB::table('mship_permission')->whereNotNull('deleted_at')->delete();
        Schema::table('mship_permission', function ($table) {
            $table->dropColumn('deleted_at');
        });

        DB::table('mship_account_note')->whereNotNull('deleted_at')->delete();
        Schema::table('mship_account_note', function ($table) {
            $table->dropColumn('deleted_at');
        });

        DB::table('mship_account_ban')->whereNotNull('deleted_at')->delete();
        Schema::table('mship_account_ban', function ($table) {
            $table->dropColumn('deleted_at');
        });

        DB::table('sys_notification')->whereNotNull('deleted_at')->delete();
        Schema::table('sys_notification', function ($table) {
            $table->dropColumn('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statistic', function ($table) {
            $table->softDeletes();
        });

        Schema::table('sso_token', function ($table) {
            $table->softDeletes();
        });

        Schema::table('sso_email', function ($table) {
            $table->softDeletes();
        });

        Schema::table('sso_account', function ($table) {
            $table->softDeletes();
        });

        Schema::table('mship_role', function ($table) {
            $table->softDeletes();
        });

        Schema::table('mship_permission', function ($table) {
            $table->softDeletes();
        });

        Schema::table('mship_account_note', function ($table) {
            $table->softDeletes();
        });

        Schema::table('mship_account_ban', function ($table) {
            $table->softDeletes();
        });

        Schema::table('sys_notification', function ($table) {
            $table->softDeletes();
        });
    }
}
