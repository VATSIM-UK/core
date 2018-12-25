<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixTeamspeakForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('teamspeak_channel_group_permission', function (Blueprint $table) {
            $table->dropForeign('teamspeak_channel_group_permission_channelgroup_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teamspeak_channel_group_permission', function (Blueprint $table) {
            $table->foreign('channelgroup_id')->references('id')->on('teamspeak_group')->onDelete('cascade');
        });
    }
}
