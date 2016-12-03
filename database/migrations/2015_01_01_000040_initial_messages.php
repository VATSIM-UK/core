<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InitialMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages_thread', function (Blueprint $table) {
            $table->bigIncrements('thread_id');
            $table->string('subject', 255);
            $table->boolean('read_only')->default(0);
            $table->timestamps();
        });

        Schema::create('messages_thread_post', function (Blueprint $table) {
            $table->bigIncrements('thread_post_id');
            $table->bigInteger('thread_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('messages_thread_participant', function (Blueprint $table) {
            $table->bigIncrements('thread_participant_id');
            $table->bigInteger('thread_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->string('display_as', 255);
            $table->smallInteger('status');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages_thread');
        Schema::dropIfExists('messages_thread_post');
        Schema::dropIfExists('messages_thread_participant');
    }
}
