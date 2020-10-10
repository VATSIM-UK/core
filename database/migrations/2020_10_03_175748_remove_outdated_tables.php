<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RemoveOutdatedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('basic_users');
        Schema::dropIfExists('community_group');
        Schema::dropIfExists('community_memberships');
        Schema::dropIfExists('email_events');

        Schema::dropIfExists('messages_thread');
        Schema::dropIfExists('messages_thread_participant');
        Schema::dropIfExists('messages_thread_post');

        Schema::dropIfExists('short_url');

        Schema::dropIfExists('staff_account_position');
        Schema::dropIfExists('staff_attribute_position');
        Schema::dropIfExists('staff_attributes');
        Schema::dropIfExists('staff_positions');

        Schema::dropIfExists('statistic');
        Schema::dropIfExists('sys_config');

        Schema::dropIfExists('sys_timeline_action');
        Schema::dropIfExists('sys_timeline_entry');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        throw new Exception('Unable to role back the remove outdated tables migration');
    }
}
