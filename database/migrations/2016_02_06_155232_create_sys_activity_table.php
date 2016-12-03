<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSysActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_activity', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('actor_id')->unsigned();
            $table->morphs('subject');
            $table->string('action', 20);
            $table->bigInteger('ip')->unsigned()->default(ip2long('0.0.0.0'));
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
        Schema::dropIfExists('sys_activity');
    }
}
