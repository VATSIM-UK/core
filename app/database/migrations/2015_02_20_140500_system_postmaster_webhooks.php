<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SystemPostmasterWebhooks extends Migration {

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

        // Let's also add the necessary timeline actions for the mailhooks.
        DB::table("sys_timeline_action")->insert(array(
                ["section" => "sys", "area" => "postmaster_queue", "action" => "queued", "version" => 1, "entry" => "Email #{extra} queued for {owner}."],
                ["section" => "sys", "area" => "postmaster_queue", "action" => "parsed", "version" => 1, "entry" => "Email #{extra} parsed and ready to send to {owner}."],
                ["section" => "sys", "area" => "postmaster_queue", "action" => "sent", "version" => 1, "entry" => "Email #{extra} sent to {owner}."],
                ["section" => "sys", "area" => "postmaster_queue", "action" => "sent_by", "version" => 1, "entry" => "{owner} sent email #{extra}."],
                ["section" => "sys", "area" => "postmaster_queue", "action" => "delivered", "version" => 1, "entry" => "Email #{extra} delivered to {owner} successfully."],
                ["section" => "sys", "area" => "postmaster_queue", "action" => "opened", "version" => 1, "entry" => "{owner} opened email #{extra}."],
                ["section" => "sys", "area" => "postmaster_queue", "action" => "clicked", "version" => 1, "entry" => "{owner} clicked a link in email #{extra}."],
                ["section" => "sys", "area" => "postmaster_queue", "action" => "dropped", "version" => 1, "entry" => "Email #{extra} was dropped whilst trying to deliver to {owner}."],
                ["section" => "sys", "area" => "postmaster_queue", "action" => "bounced", "version" => 1, "entry" => "Email #{extra} bounced when trying to deliver to {owner}."],
                ["section" => "sys", "area" => "postmaster_queue", "action" => "spam", "version" => 1, "entry" => "{owner} marked email #{extra} as spam."],
                ["section" => "sys", "area" => "postmaster_queue", "action" => "unsubscribed", "version" => 1, "entry" => "{owner} has unsubscribed from {extra} at our SMTP."]
        ));
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

        DB::table("sys_timeline_action")->where("section", "=", "sys")->where("area", "=", "postmaster_queue")->delete();
    }

}
