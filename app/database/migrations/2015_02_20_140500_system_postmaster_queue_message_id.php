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
    }

}
