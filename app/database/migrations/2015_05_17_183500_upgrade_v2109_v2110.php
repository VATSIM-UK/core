<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpgradeV2109V2110 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        // SYS_TOKEN modifications.  Issue #196
        Schema::table("sys_token", function($table){
            $table->string("type_new", 75)->after("type");
        });

        DB::table("sys_token")
            -> update(array("type_new" => DB::raw("`type`")));

        Schema::table("sys_token", function($table){
            $table->dropColumn("type");
            $table->renameColumn("type_new", "type");
        });

        // SSO_EMAIL modifications.  Issue #69
        Schema::table("sso_email", function($table){
            $table->integer("account_id")->unsigned()->after("sso_email_id");
        });
        DB::statement("ALTER TABLE `sso_email` MODIFY `account_email_id` BIGINT UNSIGNED NULL DEFAULT NULL;"); // Add NULL

        // Since we only previously associated email_id, we now need to add account_id too!
        $unassocAccounts = \Models\Sso\Email::where("account_id", "=", 0)->get();
        foreach($unassocAccounts as $ua){
            $ua->account_id = $ua->email->account_id;
            $ua->save();
        }

        // And then if we still can't do it, just bin off the other assignments.
        DB::table("sso_email")
            ->where("account_id", "=", 0)
            ->delete();

        // NOTIFICATIONS SYSTEM INTRODUCTION
        Schema::create("sys_notification", function($table){
            $table->bigIncrements("notification_id")->unsigned();
            $table->string("title", 75);
            $table->text("content");
            $table->smallInteger("status")->unsigned();
            $table->timestamps();
            $table->timestamp("effective_at")->nullable();
            $table->softDeletes();
        });
        Schema::create("sys_notification_read", function($table){
            $table->bigIncrements("notification_read_id")->unsigned();
            $table->bigInteger("notification_id")->unsigned();
            $table->integer("account_id")->unsigned();
            $table->timestamps();
            $table->unique(["notification_id", "account_id"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        // We can't undo the token changes, as it was "previously" an enum.
        // --

        Schema::table("sso_email", function($table){
            $table->dropColumn("account_id");
        });
        DB::statement("ALTER TABLE `sso_email` MODIFY `account_email_id` BIGINT UNSIGNED DEFAULT 0;"); // Remove NULL

        // REMOVE notifications system tables
        Schema::dropIfExists("sys_notification");
        Schema::dropIfExists("sys_notification_read");
    }
}
