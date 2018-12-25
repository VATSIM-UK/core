<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTeamspeakBans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::drop('teamspeak_ban');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('teamspeak_ban', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id');
            $table->string('reason', 255);
            $table->unsignedInteger('authorised_by');
            $table->nullableTimestamps();
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();

            $table->foreign('account_id', 'teamspeak_ban_account_id_foreign')->references('id')->on('mship_account')->onDelete('RESTRICT')->onUpdate('RESTRICT');
            $table->foreign('authorised_by', 'teamspeak_ban_authorised_by_foreign')->references('id')->on('mship_account')->onDelete('RESTRICT')->onUpdate('RESTRICT');
        });
    }
}
