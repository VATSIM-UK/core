<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('broker');
            $table->string('message_id')->nullable();
            $table->string('name');
            $table->string('recipient');
            $table->text('data');
            $table->timestamp('triggered_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_events');
    }
}
