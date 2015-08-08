<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SystemPostmasterInitial extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create("sys_postmaster_template", function($table) {
            $table->bigIncrements("postmaster_template_id")->unsigned();
            $table->string("section", 35);
            $table->string("area", 35);
            $table->string("action", 35);
            $table->string("subject", 200);
            $table->text("body");
            $table->smallInteger("priority")->default(\Models\Sys\Postmaster\Template::PRIORITY_MED);
            $table->boolean("secondary_emails")->default(0);
            $table->string("reply_to", 50);
            $table->boolean("enabled")->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(array("section", "area", "action"));
        });

        DB::table("sys_postmaster_template")->insert(array(
            ["section" => "mship", "area" => "account", "action" => "created", "subject" => 'Membership Account Created - CID {{{ $recipient->account_id }}}', "body" => "", "secondary_emails" => 0, "reply_to" => "", "enabled" => 0, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["section" => "mship", "area" => "security", "action" => "forgotten", "subject" => 'SSO Secondary Password Reset', "body" => "", "secondary_emails" => 0, "reply_to" => "", "enabled" => 0, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["section" => "mship", "area" => "security", "action" => "reset", "subject" => 'SSO Secondary Password Reset', "body" => "", "secondary_emails" => 0, "reply_to" => "", "enabled" => 0, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
        ));

        Schema::create("sys_postmaster_queue", function($table){
            $table->bigIncrements("postmaster_queue_id")->unsigned();
            $table->integer("recipient_id")->unsigned();
            $table->bigInteger("recipient_email_id")->unsigned();
            $table->integer("sender_id")->unsigned();
            $table->bigInteger("sender_email_id")->unsigned();
            $table->bigInteger("postmaster_template_id")->unsigned();
            $table->smallInteger("priority")->default(\Models\Sys\Postmaster\Template::PRIORITY_MED);
            $table->string("subject");
            $table->text("body");
            $table->text("data");
            $table->smallInteger("status")->default();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists("sys_postmaster_template");
        Schema::dropIfExists("sys_postmaster_queue");
    }

}
