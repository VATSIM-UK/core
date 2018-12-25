<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // since we didn't use the old table, and it already doesn't work with the current version, just recreate it
        Schema::drop('sys_sessions');
        Schema::create('sys_sessions', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->integer('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
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
        Schema::drop('sys_sessions');
        Schema::create('sys_sessions', function ($table) {
            $table->string('id')->unique();
            $table->text('payload');
            $table->integer('last_activity');
        });
    }
}
