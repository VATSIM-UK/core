<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveAllForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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
            $table->dropForeign('teamspeak_channel_group_permission_permission_id_foreign');
        });

        Schema::table('teamspeak_channel', function (Blueprint $table) {
            $table->dropForeign('teamspeak_channel_parent_id_foreign');
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

        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropForeign('oauth_clients_user_id_foreign');
        });

        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->dropForeign('oauth_auth_codes_user_id_foreign');
            $table->dropForeign('oauth_auth_codes_client_id_foreign');
        });

        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->dropForeign('oauth_access_tokens_user_id_foreign');
            $table->dropForeign('oauth_access_tokens_client_id_foreign');
        });

        Schema::table('networkdata_atc', function (Blueprint $table) {
            $table->dropForeign('networkdata_atc_account_id_foreign');
            $table->dropForeign('networkdata_atc_qualification_id_foreign');
        });

        Schema::table('networkdata_pilots', function (Blueprint $table) {
            $table->dropForeign('networkdata_pilots_account_id_foreign');
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

        Schema::table('mship_feedback_answers', function (Blueprint $table) {
            $table->dropForeign('mship_feedback_answers_feedback_id_foreign');
            $table->dropForeign('mship_feedback_answers_question_id_foreign');
        });

        Schema::table('mship_feedback', function (Blueprint $table) {
            $table->dropForeign('mship_feedback_form_id_foreign');
            $table->dropForeign('mship_feedback_account_id_foreign');
            $table->dropForeign('mship_feedback_submitter_account_id_foreign');
            $table->dropForeign('mship_feedback_actioned_by_id_foreign');
            $table->dropForeign('mship_feedback_sent_by_id_foreign');
        });

        Schema::table('mship_feedback_forms', function (Blueprint $table) {
            $table->dropForeign('mship_feedback_forms_contact_id_foreign');
        });

        Schema::table('mship_account_state', function (Blueprint $table) {
            $table->dropForeign('mship_account_state_account_id_foreign');
            $table->dropForeign('mship_account_state_state_id_foreign');
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

        Schema::table('mship_account_email', function (Blueprint $table) {
            $table->dropForeign('mship_account_email_account_id_foreign');
        });

        Schema::table('mship_account_ban', function (Blueprint $table) {
            $table->dropForeign('mship_account_ban_account_id_foreign');
            $table->dropForeign('mship_account_ban_banned_by_foreign');
            $table->dropForeign('mship_account_ban_reason_id_foreign');
        });

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

        Schema::table('smartcars_bid', function (Blueprint $table) {
            $table->dropForeign(['flight_id']);
            $table->dropForeign(['account_id']);
        });

        Schema::table('smartcars_flight', function (Blueprint $table) {
            $table->dropForeign(['departure_id']);
            $table->dropForeign(['arrival_id']);
            $table->dropForeign(['aircraft_id']);
        });

        Schema::table('smartcars_flight_criteria', function (Blueprint $table) {
            $table->dropForeign(['flight_id']);
        });

        Schema::table('smartcars_pirep', function (Blueprint $table) {
            $table->dropForeign(['bid_id']);
            $table->dropForeign(['aircraft_id']);
            $table->dropForeign(['failed_at']);
        });

        Schema::table('smartcars_posrep', function (Blueprint $table) {
            $table->dropForeign(['bid_id']);
            $table->dropForeign(['aircraft_id']);
        });

        Schema::table('smartcars_session', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
