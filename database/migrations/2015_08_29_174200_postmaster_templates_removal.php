<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use \Models\Mship\Account;
use \Models\Mship\Account\Ban as AccountBan;

class PostmasterTemplatesRemoval extends Migration {

    public function up()
    {
        Schema::rename("sys_postmaster_template", "sys_postmaster_setting");
        Schema::table('sys_postmaster_setting', function ($table) {
            $table->renameColumn("postmaster_template_id", "postmaster_setting_id");
            $table->dropColumn("subject");
            $table->dropColumn("body");
            $table->text("description")->after("action");
        });
    }

    public function down()
    {
        // We lose data in the migration, as such only the structure can be restored NOT the data contained within it.
        Schema::rename("sys_postmaster_setting", "sys_postmaster_template");

        Schema::table('sys_postmaster_template', function ($table) {
            $table->renameColumn("postmaster_setting_id", "postmaster_template_id");
            $table->dropColumn("description");
            $table->string("subject")->after("action");
            $table->text("body")->after("subject");
        });
    }
}