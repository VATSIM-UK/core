<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StrictMode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE mship_account_ban MODIFY period_start TIMESTAMP NULL DEFAULT NULL');
        DB::statement('ALTER TABLE teamspeak_ban MODIFY expires_at TIMESTAMP NULL DEFAULT NULL');
        DB::statement('ALTER TABLE jobs_failed MODIFY failed_at TIMESTAMP NULL DEFAULT NULL');
        DB::statement('ALTER TABLE community_group MODIFY coordinate_boundaries LONGTEXT NULL DEFAULT NULL');
        DB::statement('ALTER TABLE email_events MODIFY triggered_at TIMESTAMP NULL DEFAULT NULL');
        DB::statement('ALTER TABLE networkdata_atc MODIFY facility_type TINYINT UNSIGNED NULL DEFAULT NULL');
        DB::statement('ALTER TABLE vt_application MODIFY status_note TEXT NULL DEFAULT NULL');
        DB::statement('ALTER TABLE vt_reference MODIFY status_note TEXT NULL DEFAULT NULL');

        Schema::table('teamspeak_group', function (Blueprint $table) {
            $table->boolean('protected')->default(false)->change();
        });

        Schema::table('messages_thread_participant', function (Blueprint $table) {
            $table->string('display_as')->nullable()->change();
            $table->smallInteger('status')->nullable()->change();
        });

        Schema::table('sys_notification', function (Blueprint $table) {
            $table->text('content')->nullable()->change();
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
