<?php

use Illuminate\Database\Migrations\Migration;

class InitialSystem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_timeline_entry', function ($table) {
            $table->bigIncrements('timeline_entry_id')->unsigned();
            $table->integer('timeline_action_id')->unsigned();
            $table->morphs('owner');
            $table->morphs('extra');
            $table->text('extra_data');
            $table->integer('ip');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sys_timeline_action', function ($table) {
            $table->increments('timeline_action_id')->unsigned();
            $table->string('section', 35);
            $table->string('area', 35);
            $table->string('action', 35);
            $table->smallInteger('version');
            $table->text('entry');
            $table->boolean('enabled')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['section', 'area', 'action']);
        });

        DB::table('sys_timeline_action')->insert([
            ['section' => 'mship', 'area' => 'account', 'action' => 'created', 'version' => 1, 'entry' => "{owner}'s account was created!", 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'mship', 'area' => 'account', 'action' => 'updated', 'version' => 1, 'entry' => "{owner}'s account was updated.", 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'postmaster_queue', 'action' => 'created', 'version' => 1, 'entry' => '{owner} email was queued for dispatch to {extra}.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'postmaster_queue', 'action' => 'updated', 'version' => 1, 'entry' => '{owner} email was updated.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'token', 'action' => 'created', 'version' => 1, 'entry' => '{owner} token created for {extra}.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'token', 'action' => 'updated', 'version' => 1, 'entry' => '{owner} token updated for {extra}.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'token', 'action' => 'deleted', 'version' => 1, 'entry' => '{owner} token deleted for {extra}.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'postmaster_queue', 'action' => 'queued', 'version' => 1, 'entry' => 'Email #{extra} queued for {owner}.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'postmaster_queue', 'action' => 'parsed', 'version' => 1, 'entry' => 'Email #{extra} parsed and ready to send to {owner}.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'postmaster_queue', 'action' => 'sent', 'version' => 1, 'entry' => 'Email #{extra} sent to {owner}.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'postmaster_queue', 'action' => 'sent_by', 'version' => 1, 'entry' => '{owner} sent email #{extra}.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'postmaster_queue', 'action' => 'delivered', 'version' => 1, 'entry' => 'Email #{extra} delivered to {owner} successfully.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'postmaster_queue', 'action' => 'opened', 'version' => 1, 'entry' => '{owner} opened email #{extra}.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'postmaster_queue', 'action' => 'clicked', 'version' => 1, 'entry' => '{owner} clicked a link in email #{extra}.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'postmaster_queue', 'action' => 'dropped', 'version' => 1, 'entry' => 'Email #{extra} was dropped whilst trying to deliver to {owner}.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'postmaster_queue', 'action' => 'bounced', 'version' => 1, 'entry' => 'Email #{extra} bounced when trying to deliver to {owner}.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'postmaster_queue', 'action' => 'spam', 'version' => 1, 'entry' => '{owner} marked email #{extra} as spam.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'postmaster_queue', 'action' => 'unsubscribed', 'version' => 1, 'entry' => '{owner} has unsubscribed from {extra} at our SMTP.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'mship', 'area' => 'account', 'action' => 'impersonate', 'version' => 1, 'entry' => '{owner} impersonated {extra} and logged into their basic user account.  A reason was given.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'postmaster_error', 'action' => 'parse', 'version' => 1, 'entry' => 'Email #{extra} failed to parse when being prepared for {owner}.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
            ['section' => 'sys', 'area' => 'postmaster_error', 'action' => 'dispatch', 'version' => 1, 'entry' => 'Email #{extra} failed to send to {owner}.', 'enabled' => 1, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now()],
        ]);

        Schema::create('sys_token', function ($table) {
            $table->increments('token_id')->unsigned();
            $table->morphs('related');
            $table->string('type');
            $table->string('code', 31);
            $table->timestamps();
            $table->timestamp('expires_at')->nullable;
            $table->timestamp('used_at')->nullable();
            $table->softDeletes();
            $table->unique('code');
        });

        Schema::create('sys_data_change', function ($table) {
            $table->bigIncrements('data_change_id')->unsigned();
            $table->morphs('model');
            $table->string('data_key', 100);
            $table->text('data_old')->nullable();
            $table->text('data_new')->nullable();
            $table->boolean('automatic')->default(0);
            $table->timestamps();
        });

        Schema::create('sys_notification', function ($table) {
            $table->bigIncrements('notification_id')->unsigned();
            $table->string('title', 75);
            $table->text('content');
            $table->smallInteger('status')->unsigned();
            $table->timestamps();
            $table->timestamp('effective_at')->nullable();
            $table->softDeletes();
        });
        Schema::create('sys_notification_read', function ($table) {
            $table->bigIncrements('notification_read_id')->unsigned();
            $table->bigInteger('notification_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->timestamps();
            $table->unique(['notification_id', 'account_id']);
        });

        Schema::create('sys_sessions', function ($table) {
            $table->string('id')->unique();
            $table->text('payload');
            $table->integer('last_activity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_timeline_entry');
        Schema::dropIfExists('sys_timeline_action');
        Schema::dropIfExists('sys_token');
        Schema::dropIfExists('sys_data_change');
        Schema::dropIfExists('sys_notification');
        Schema::dropIfExists('sys_notification_read');
        Schema::dropIfExists('sys_sessions');
    }
}
