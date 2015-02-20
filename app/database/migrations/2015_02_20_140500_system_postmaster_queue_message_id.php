<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SystemPostmasterQueueMessageId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table("sys_postmaster_queue", function($table){
           $table->string("message_id")->after("postmaster_queue_id");
        });

        // Let's also move all queue status from 90 => 50!
        DB::update("UPDATE `sys_postmaster_queue` SET `status` = '50' WHERE `status` = '90'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table("sys_postmaster_queue", function($table){
           $table->dropColumn("message_id");
        });

        // Let's also move all queue status from 50 => 90!
        DB::update("UPDATE `sys_postmaster_queue` SET `status` = '90' WHERE `status` = '50'");
    }

}
