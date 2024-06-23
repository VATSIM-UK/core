<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        DB::statement('ALTER TABLE api_request MODIFY method VARCHAR(10)');
        Schema::table('api_request', function (Blueprint $table) {
            $table->unsignedInteger('api_account_id')->change();
            $table->foreign('api_account_id')->references('id')->on('mship_account');
        });

        Schema::table('community_membership', function (Blueprint $table) {
            $table->foreign('group_id')->references('id')->on('community_group')->onDelete('cascade');
        });

        Schema::table('messages_thread_participant', function (Blueprint $table) {
            $table->foreign('thread_id')->references('id')->on('messages_thread')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('mship_account');
        });

        Schema::table('messages_thread_post', function (Blueprint $table) {
            $table->foreign('thread_id')->references('id')->on('messages_thread')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('mship_account');
        });

        DB::statement('ALTER TABLE mship_account_ban MODIFY account_id INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE mship_account_ban MODIFY banned_by INT UNSIGNED');
        DB::table('mship_account_ban')->where('banned_by', 0)->update(['banned_by' => null]);
        Schema::table('mship_account_ban', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('mship_account');
            $table->foreign('banned_by')->references('id')->on('mship_account');
            $table->foreign('reason_id')->references('id')->on('mship_ban_reason');
        });

        Schema::table('mship_account_email', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('mship_account');
        });

        Schema::table('mship_account_note', function (Blueprint $table) {
            $table->unsignedInteger('writer_id')->nullable()->change();
        });
        DB::table('mship_account_note')->where('writer_id', 0)->update(['writer_id' => null]);
        Schema::table('mship_account_note', function (Blueprint $table) {
            $table->foreign('note_type_id')->references('id')->on('mship_note_type');
            $table->foreign('account_id')->references('id')->on('mship_account');
            $table->foreign('writer_id')->references('id')->on('mship_account');
        });

        DB::statement('delete from mship_account_qualification where account_id not in (select id from mship_account)');
        DB::statement('delete from mship_account_qualification where qualification_id not in (select id from mship_qualification)');
        Schema::table('mship_account_qualification', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('mship_account');
            $table->foreign('qualification_id')->references('id')->on('mship_qualification');
        });

        DB::statement('delete from mship_account_role where account_id not in (select id from mship_account)');
        Schema::table('mship_account_role', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('mship_account');
            $table->foreign('role_id')->references('id')->on('mship_role')->onDelete('cascade');
        });

        DB::statement('delete from mship_account_state where account_id not in (select id from mship_account)');
        Schema::table('mship_account_state', function (Blueprint $table) {
            $table->unsignedInteger('state_id')->change();
            $table->foreign('account_id')->references('id')->on('mship_account');
            $table->foreign('state_id')->references('id')->on('mship_state');
        });

        Schema::table('mship_feedback', function (Blueprint $table) {
            $table->unsignedBigInteger('form_id')->change();
            $table->foreign('form_id')->references('id')->on('mship_feedback_forms');
            $table->foreign('account_id')->references('id')->on('mship_account');
            $table->foreign('submitter_account_id')->references('id')->on('mship_account');
            $table->foreign('actioned_by_id')->references('id')->on('mship_account');
        });

        Schema::table('mship_feedback_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('feedback_id')->change();
            $table->foreign('feedback_id')->references('id')->on('mship_feedback');
            $table->foreign('question_id')->references('id')->on('mship_feedback_questions');
        });

        Schema::table('mship_feedback_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('form_id')->change();
            $table->foreign('type_id')->references('id')->on('mship_feedback_question_types');
            $table->foreign('form_id')->references('id')->on('mship_feedback_forms');
        });

        DB::statement('delete from mship_oauth_emails where sso_account_id not in (select id from oauth_clients)');
        Schema::table('mship_oauth_emails', function (Blueprint $table) {});

        Schema::table('mship_permission_role', function (Blueprint $table) {
            $table->foreign('permission_id')->references('id')->on('mship_permission')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('mship_role')->onDelete('cascade');
        });

        Schema::table('networkdata_atc', function (Blueprint $table) {
            $table->unsignedInteger('qualification_id')->change();
            $table->foreign('account_id')->references('id')->on('mship_account');
            $table->foreign('qualification_id')->references('id')->on('mship_qualification');
        });

        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->change();
            $table->unsignedInteger('client_id')->change();
            $table->foreign('user_id')->references('id')->on('mship_account');
        });

        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->change();
            $table->unsignedInteger('client_id')->change();
            $table->foreign('user_id')->references('id')->on('mship_account');
        });

        DB::table('oauth_clients')->where('user_id', 0)->update(['user_id' => null]);
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->change();
            $table->foreign('user_id')->references('id')->on('mship_account');
        });

        Schema::table('oauth_personal_access_clients', function (Blueprint $table) {
            $table->unsignedInteger('client_id')->change();
        });

        Schema::table('oauth_refresh_tokens', function (Blueprint $table) {
            $table->foreign('access_token_id')->references('id')->on('oauth_access_tokens')->onDelete('cascade');
        });

        Schema::table('staff_account_position', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('mship_account');
            $table->foreign('position_id')->references('id')->on('staff_positions')->onDelete('cascade');
        });

        Schema::table('staff_attribute_position', function (Blueprint $table) {
            $table->foreign('attribute_id')->references('id')->on('staff_attributes')->onDelete('cascade');
            $table->foreign('position_id')->references('id')->on('staff_positions')->onDelete('cascade');
        });

        Schema::table('staff_attributes', function (Blueprint $table) {
            $table->foreign('service_id')->references('id')->on('staff_services')->onDelete('cascade');
        });

        Schema::table('staff_positions', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('staff_positions')->onDelete('set null');
        });

        Schema::table('sys_activity', function (Blueprint $table) {
            $table->unsignedInteger('actor_id')->nullable()->change();
        });
        DB::table('sys_activity')->where('actor_id', 0)->update(['actor_id' => null]);
        Schema::table('sys_activity', function (Blueprint $table) {
            $table->foreign('actor_id')->references('id')->on('mship_account');
        });

        Schema::table('sys_notification_read', function (Blueprint $table) {
            $table->foreign('notification_id')->references('id')->on('sys_notification')->onDelete('cascade');
        });

        Schema::table('sys_timeline_entry', function (Blueprint $table) {
            $table->foreign('timeline_action_id')->references('timeline_action_id')->on('sys_timeline_action');
        });

        Schema::table('teamspeak_ban', function (Blueprint $table) {
            $table->dropIndex('teamspeak_ban_account_id_index');
        });
        Schema::table('teamspeak_ban', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('mship_account');
            $table->foreign('authorised_by')->references('id')->on('mship_account');
        });

        DB::table('teamspeak_channel')->where('parent_id', 0)->update(['parent_id' => null]);
        Schema::table('teamspeak_channel', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('teamspeak_channel')->onDelete('set null');
        });

        Schema::table('teamspeak_channel_group_permission', function (Blueprint $table) {
            $table->foreign('channel_id')->references('id')->on('teamspeak_channel')->onDelete('cascade');
            $table->foreign('channelgroup_id')->references('id')->on('teamspeak_group')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('mship_permission')->onDelete('cascade');
        });

        Schema::table('teamspeak_confirmation', function (Blueprint $table) {
            $table->foreign('registration_id')->references('id')->on('teamspeak_registration')->onDelete('cascade');
        });

        Schema::table('teamspeak_group', function (Blueprint $table) {
            $table->foreign('permission_id')->references('id')->on('mship_permission')->onDelete('set null');
            $table->foreign('qualification_id')->references('id')->on('mship_qualification')->onDelete('set null');
        });

        Schema::table('teamspeak_registration', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('mship_account');
        });

        Schema::table('vt_application', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('mship_account');
            $table->foreign('facility_id')->references('id')->on('vt_facility');
        });

        Schema::table('vt_facility_email', function (Blueprint $table) {
            $table->foreign('facility_id')->references('id')->on('vt_facility');
        });

        Schema::table('vt_reference', function (Blueprint $table) {
            $table->foreign('application_id')->references('id')->on('vt_application');
            $table->foreign('account_id')->references('id')->on('mship_account');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vt_reference', function (Blueprint $table) {
            $table->dropForeign('vt_reference_application_id_foreign');
            $table->dropForeign('vt_reference_account_id_foreign');
        });

        Schema::table('vt_facility_email', function (Blueprint $table) {
            $table->dropForeign('vt_facility_email_facility_id_foreign');
        });

        Schema::table('vt_application', function (Blueprint $table) {
            $table->dropForeign('vt_application_account_id_foreign');
            $table->dropForeign('vt_application_facility_id_foreign');
        });

        Schema::table('teamspeak_registration', function (Blueprint $table) {
            $table->dropForeign('teamspeak_registration_account_id_foreign');
        });

        Schema::table('teamspeak_group', function (Blueprint $table) {
            $table->dropForeign('teamspeak_group_permission_id_foreign');
            $table->dropForeign('teamspeak_group_qualification_id_foreign');
        });

        Schema::table('teamspeak_confirmation', function (Blueprint $table) {
            $table->dropForeign('teamspeak_confirmation_registration_id_foreign');
        });

        Schema::table('teamspeak_channel_group_permission', function (Blueprint $table) {
            $table->dropForeign('teamspeak_channel_group_permission_channel_id_foreign');
            $table->dropForeign('teamspeak_channel_group_permission_channelgroup_id_foreign');
            $table->dropForeign('teamspeak_channel_group_permission_permission_id_foreign');
        });

        Schema::table('teamspeak_channel', function (Blueprint $table) {
            $table->dropForeign('teamspeak_channel_parent_id_foreign');
        });
        DB::table('teamspeak_channel')->where('parent_id', null)->update(['parent_id' => 0]);

        Schema::table('teamspeak_ban', function (Blueprint $table) {
            $table->dropForeign('teamspeak_ban_account_id_foreign');
            $table->dropForeign('teamspeak_ban_authorised_by_foreign');
        });
        Schema::table('teamspeak_ban', function (Blueprint $table) {
            $table->integer('account_id')->unsigned()->index()->change();
        });

        Schema::table('sys_timeline_entry', function (Blueprint $table) {
            $table->dropForeign('sys_timeline_entry_timeline_action_id_foreign');
        });

        Schema::table('sys_notification_read', function (Blueprint $table) {
            $table->dropForeign('sys_notification_read_notification_id_foreign');
        });

        Schema::table('sys_activity', function (Blueprint $table) {
            $table->dropForeign('sys_activity_actor_id_foreign');
        });
        DB::table('sys_activity')->where('actor_id', null)->update(['actor_id' => 0]);
        Schema::table('sys_activity', function (Blueprint $table) {
            $table->unsignedInteger('actor_id')->change();
        });

        Schema::table('staff_positions', function (Blueprint $table) {
            $table->dropForeign('staff_positions_parent_id_foreign');
        });

        Schema::table('staff_attributes', function (Blueprint $table) {
            $table->dropForeign('staff_attributes_service_id_foreign');
        });

        Schema::table('staff_attribute_position', function (Blueprint $table) {
            $table->dropForeign('staff_attribute_position_attribute_id_foreign');
            $table->dropForeign('staff_attribute_position_position_id_foreign');
        });

        Schema::table('staff_account_position', function (Blueprint $table) {
            $table->dropForeign('staff_account_position_account_id_foreign');
            $table->dropForeign('staff_account_position_position_id_foreign');
        });

        Schema::table('oauth_refresh_tokens', function (Blueprint $table) {
            $table->dropForeign('oauth_refresh_tokens_access_token_id_foreign');
        });

        Schema::table('oauth_personal_access_clients', function (Blueprint $table) {
            $table->dropForeign('oauth_personal_access_clients_client_id_foreign');
        });
        Schema::table('oauth_personal_access_clients', function (Blueprint $table) {
            $table->integer('client_id')->change();
        });

        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropForeign('oauth_clients_user_id_foreign');
        });
        DB::table('oauth_clients')->where('user_id', null)->update(['user_id' => 0]);
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->integer('user_id')->change();
        });

        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->dropForeign('oauth_auth_codes_user_id_foreign');
            $table->dropForeign('oauth_auth_codes_client_id_foreign');
        });
        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->integer('user_id')->change();
            $table->integer('client_id')->change();
        });

        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->dropForeign('oauth_access_tokens_user_id_foreign');
            $table->dropForeign('oauth_access_tokens_client_id_foreign');
        });
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->integer('user_id')->change();
            $table->integer('client_id')->change();
        });

        Schema::table('networkdata_atc', function (Blueprint $table) {
            $table->dropForeign('networkdata_atc_account_id_foreign');
            $table->dropForeign('networkdata_atc_qualification_id_foreign');
        });
        Schema::table('networkdata_atc', function (Blueprint $table) {
            $table->unsignedSmallInteger('qualification_id')->change();
        });

        Schema::table('mship_permission_role', function (Blueprint $table) {
            $table->dropForeign('mship_permission_role_permission_id_foreign');
            $table->dropForeign('mship_permission_role_role_id_foreign');
        });

        Schema::table('mship_oauth_emails', function (Blueprint $table) {
            $table->dropForeign('mship_oauth_emails_account_email_id_foreign');
            $table->dropForeign('mship_oauth_emails_sso_account_id_foreign');
        });

        Schema::table('mship_feedback_questions', function (Blueprint $table) {
            $table->dropForeign('mship_feedback_questions_type_id_foreign');
            $table->dropForeign('mship_feedback_questions_form_id_foreign');
        });
        Schema::table('mship_feedback_questions', function (Blueprint $table) {
            $table->unsignedInteger('form_id')->change();
        });

        Schema::table('mship_feedback_answers', function (Blueprint $table) {
            $table->dropForeign('mship_feedback_answers_feedback_id_foreign');
            $table->dropForeign('mship_feedback_answers_question_id_foreign');
        });
        Schema::table('mship_feedback_answers', function (Blueprint $table) {
            $table->unsignedInteger('feedback_id')->change();
        });

        Schema::table('mship_feedback', function (Blueprint $table) {
            $table->dropForeign('mship_feedback_form_id_foreign');
            $table->dropForeign('mship_feedback_account_id_foreign');
            $table->dropForeign('mship_feedback_submitter_account_id_foreign');
            $table->dropForeign('mship_feedback_actioned_by_id_foreign');
        });
        Schema::table('mship_feedback', function (Blueprint $table) {
            $table->unsignedInteger('form_id')->change();
        });

        Schema::table('mship_account_state', function (Blueprint $table) {
            $table->dropForeign('mship_account_state_account_id_foreign');
            $table->dropForeign('mship_account_state_state_id_foreign');
        });
        Schema::table('mship_account_state', function (Blueprint $table) {
            $table->integer('state_id')->change();
        });

        Schema::table('mship_account_role', function (Blueprint $table) {
            $table->dropForeign('mship_account_role_account_id_foreign');
            $table->dropForeign('mship_account_role_role_id_foreign');
        });

        Schema::table('mship_account_qualification', function (Blueprint $table) {
            $table->dropForeign('mship_account_qualification_account_id_foreign');
            $table->dropForeign('mship_account_qualification_qualification_id_foreign');
        });

        Schema::table('mship_account_note', function (Blueprint $table) {
            $table->dropForeign('mship_account_note_note_type_id_foreign');
            $table->dropForeign('mship_account_note_account_id_foreign');
            $table->dropForeign('mship_account_note_writer_id_foreign');
        });
        DB::table('mship_account_note')->where('writer_id', null)->update(['writer_id' => 0]);
        Schema::table('mship_account_note', function (Blueprint $table) {
            $table->unsignedInteger('writer_id')->change();
        });

        Schema::table('mship_account_email', function (Blueprint $table) {
            $table->dropForeign('mship_account_email_account_id_foreign');
        });

        Schema::table('mship_account_ban', function (Blueprint $table) {
            $table->dropForeign('mship_account_ban_account_id_foreign');
            $table->dropForeign('mship_account_ban_banned_by_foreign');
            $table->dropForeign('mship_account_ban_reason_id_foreign');
        });
        DB::table('mship_account_ban')->where('banned_by', null)->update(['banned_by' => 0]);
        DB::statement('ALTER TABLE mship_account_ban MODIFY account_id MEDIUMINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE mship_account_ban MODIFY banned_by MEDIUMINT UNSIGNED NOT NULL');

        Schema::table('messages_thread_post', function (Blueprint $table) {
            $table->dropForeign('messages_thread_post_thread_id_foreign');
            $table->dropForeign('messages_thread_post_account_id_foreign');
        });

        Schema::table('messages_thread_participant', function (Blueprint $table) {
            $table->dropForeign('messages_thread_participant_thread_id_foreign');
            $table->dropForeign('messages_thread_participant_account_id_foreign');
        });

        Schema::table('community_membership', function (Blueprint $table) {
            $table->dropForeign('community_membership_group_id_foreign');
        });

        Schema::table('api_request', function (Blueprint $table) {
            $table->dropForeign('api_request_api_account_id_foreign');
        });
        Schema::table('api_request', function (Blueprint $table) {
            $table->integer('api_account_id')->change();
        });
        DB::statement('ALTER TABLE api_request MODIFY method ENUM(\'POST\',\'GET\',\'PUT\',\'PATCH\',\'DELETE\') NOT NULL');
    }
}
