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
    }
}
