<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveMshipAccountEmailSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('sys_email_setting');
        Schema::dropIfExists('mship_account_email_setting');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('sys_email_setting', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('code', 100);
            $table->text('description');
            $table->boolean('enabled')->default(0);
            $table->timestamps();
        });

        Schema::create('mship_account_email_setting', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->integer('email_setting_id')->unsigned();
            $table->timestamps();
        });
    }
}
