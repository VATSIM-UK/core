<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IdRefactoring extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statistic', function ($table) {
            $table->renameColumn('statistic_id', 'id');
        });

        Schema::table('sso_token', function ($table) {
            $table->renameColumn('sso_token_id', 'id');
        });

        Schema::table('sso_email', function ($table) {
            $table->renameColumn('sso_email_id', 'id');
        });

        Schema::table('sso_account', function ($table) {
            $table->renameColumn('sso_account_id', 'id');
        });

        Schema::table('mship_role', function ($table) {
            $table->renameColumn('role_id', 'id');
        });

        Schema::table('mship_permission_role', function ($table) {
            $table->renameColumn('permission_role_id', 'id');
        });

        Schema::table('mship_permission', function ($table) {
            $table->renameColumn('permission_id', 'id');
        });

        DB::statement('ALTER TABLE mship_note_type CHANGE note_type_id id INTEGER UNSIGNED AUTO_INCREMENT');

        try {
            Schema::table('mship_account_ban', function (Blueprint $table) {
                $table->dropForeign('mship_account_ban_reason_id_foreign');
            });
        } catch (Exception $e) {
            // Do nothing with the lack of a foreign key.
        }

        DB::statement('ALTER TABLE mship_ban_reason CHANGE ban_reason_id id INTEGER UNSIGNED AUTO_INCREMENT');
//        Schema::table('mship_account_ban', function(Blueprint $table) {
//            $table->foreign('reason_id')->references('id')->on('mship_ban_reason')
//                ->onDelete('restrict')
//                ->onUpdate('restrict');
//        });
        // No more foreign keys. They break everything.

        Schema::table('mship_account_state', function ($table) {
            $table->renameColumn('account_state_id', 'id');
        });

        Schema::table('mship_account_role', function ($table) {
            $table->renameColumn('account_role_id', 'id');
        });

        Schema::table('mship_account_qualification', function ($table) {
            $table->renameColumn('account_qualification_id', 'id');
        });

        Schema::table('mship_account_note', function ($table) {
            $table->renameColumn('account_note_id', 'id');
        });

        Schema::table('mship_account_ban', function ($table) {
            $table->renameColumn('account_ban_id', 'id');
        });

        Schema::table('messages_thread', function ($table) {
            $table->renameColumn('thread_id', 'id');
        });

        Schema::table('messages_thread_participant', function ($table) {
            $table->renameColumn('thread_participant_id', 'id');
        });

        Schema::table('messages_thread_post', function ($table) {
            $table->renameColumn('thread_post_id', 'id');
        });

        Schema::table('sys_notification', function ($table) {
            $table->renameColumn('notification_id', 'id');
        });

        Schema::table('sys_notification_read', function ($table) {
            $table->renameColumn('notification_read_id', 'id');
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
            $table->renameColumn('id', 'statistic_id');
        });

        Schema::table('sso_token', function ($table) {
            $table->renameColumn('id', 'sso_token_id');
        });

        Schema::table('sso_email', function ($table) {
            $table->renameColumn('id', 'sso_email_id');
        });

        Schema::table('sso_account', function ($table) {
            $table->renameColumn('id', 'sso_account_id');
        });

        Schema::table('mship_role', function ($table) {
            $table->renameColumn('id', 'role_id');
        });

        Schema::table('mship_permission_role', function ($table) {
            $table->renameColumn('id', 'permission_role_id');
        });

        Schema::table('mship_permission', function ($table) {
            $table->renameColumn('id', 'permission_id');
        });

        DB::statement('ALTER TABLE mship_note_type CHANGE id note_type_id INTEGER UNSIGNED AUTO_INCREMENT');

//        Schema::table('mship_account_ban', function(Blueprint $table) {
//            $table->dropForeign('mship_account_ban_reason_id_foreign');
//        });
        DB::statement('ALTER TABLE mship_ban_reason CHANGE id ban_reason_id INTEGER UNSIGNED AUTO_INCREMENT');
//        Schema::table('mship_account_ban', function(Blueprint $table) {
//            $table->foreign('reason_id')->references('ban_reason_id')->on('mship_ban_reason')
//                ->onDelete('restrict')
//                ->onUpdate('restrict');
//        });

        Schema::table('mship_account_state', function ($table) {
            $table->renameColumn('id', 'account_state_id');
        });

        Schema::table('mship_account_role', function ($table) {
            $table->renameColumn('id', 'account_role_id');
        });

        Schema::table('mship_account_qualification', function ($table) {
            $table->renameColumn('id', 'account_qualification_id');
        });

        Schema::table('mship_account_note', function ($table) {
            $table->renameColumn('id', 'account_note_id');
        });

        Schema::table('mship_account_ban', function ($table) {
            $table->renameColumn('id', 'account_ban_id');
        });

        Schema::table('messages_thread', function ($table) {
            $table->renameColumn('id', 'thread_id');
        });

        Schema::table('messages_thread_participant', function ($table) {
            $table->renameColumn('id', 'thread_participant_id');
        });

        Schema::table('messages_thread_post', function ($table) {
            $table->renameColumn('id', 'thread_post_id');
        });

        Schema::table('sys_notification', function ($table) {
            $table->renameColumn('id', 'notification_id');
        });

        Schema::table('sys_notification_read', function ($table) {
            $table->renameColumn('id', 'notification_read_id');
        });
    }
}
