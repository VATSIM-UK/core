<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VanillaMshipV221 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("mship_account", function($table){
            $table->integer("account_id")->unsigned()->primary();
            $table->string("name_first", 50);
            $table->string("name_last", 50);
            $table->string("session_id");
            $table->timestamp("last_login")->nullable();
            $table->bigInteger("last_login_ip")->unsigned();
            $table->string("remember_token", 100);
            $table->boolean("auth_extra")->default(0);
            $table->timestamp("auth_extra_at")->nullable();
            $table->enum("gender", array("M", "F"))->nullable();
            $table->enum("experience", array("N", "A", "P", "B"));
            $table->smallInteger("age")->unsigned();
            $table->string("template", 10);
            $table->smallInteger("status")->unsigned();
            $table->boolean("is_invisible");
            $table->boolean("debug")->default(false);
            $table->timestamp("joined_at");
            $table->timestamps();
            $table->timestamp("cert_checked_at")->nullable();
            $table->softDeletes();
        });

        Schema::create("mship_account_email", function($table){
            $table->bigIncrements("account_email_id")->unsigned();
            $table->integer("account_id")->unsigned();
            $table->string("email", 80);
            $table->boolean("is_primary")->default(0);
            $table->timestamp("verified_at")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create("mship_account_qualification", function($table){
            $table->bigIncrements("account_qualification_id")->unsigned();
            $table->integer("account_id")->unsigned();
            $table->integer('qualification_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create("mship_account_security", function($table){
            $table->bigIncrements("account_security_id")->unsigned();
            $table->integer("account_id")->unsigned();
            $table->integer("security_id");
            $table->string("value", 40);
            $table->timestamps();
            $table->timestamp("expires_at");
            $table->softDeletes();
        });

        Schema::create("mship_account_state", function($table){
            $table->bigIncrements("account_state_id")->unsigned();
            $table->integer("account_id")->unsigned();
            $table->tinyInteger("state");
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create("mship_qualification", function($table) {
            $table->increments("qualification_id");
            $table->string("code", 3)->unique();
            $table->enum("type", array("atc", "pilot", "training_atc", "training_pilot", "admin"));
            $table->string("name_small", 15);
            $table->string("name_long", 25);
            $table->string("name_grp", 40);
            $table->smallInteger("vatsim");
            $table->timestamps();
            $table->softDeletes();
            $table->unique(array("type", "vatsim"));
        });

        DB::table("mship_qualification")->insert(array(
            ["code" => "OBS", "type" => "atc", "name_small" => "OBS", "name_long" => "Observer", "name_grp" => "Observer", "vatsim" => 1, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "S1", "type" => "atc", "name_small" => "STU", "name_long" => "Student 1", "name_grp" => "Ground Controller", "vatsim" => 2, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "S2", "type" => "atc", "name_small" => "STU2", "name_long" => "Student 2", "name_grp" => "Tower Controller", "vatsim" => 3, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "S3", "type" => "atc", "name_small" => "STU+", "name_long" => "Student 3", "name_grp" => "Approach Controller", "vatsim" => 4, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "C1", "type" => "atc", "name_small" => "CTR", "name_long" => "Controller 1", "name_grp" => "Area Controller", "vatsim" => 5, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "C3", "type" => "atc", "name_small" => "CTR+", "name_long" => "Senior Controller", "name_grp" => "Senior Controller", "vatsim" => 7, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],

            ["code" => "I1", "type" => "training_atc", "name_small" => "INS", "name_long" => "Instructor", "name_grp" => "Instructor", "vatsim" => 8, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "I3", "type" => "training_atc", "name_small" => "INS+", "name_long" => "Senior Instructor", "name_grp" => "Senior Instructor", "vatsim" => 10, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],

            ["code" => "SUP", "type" => "admin", "name_small" => "SUP", "name_long" => "Supervisor", "name_grp" => "Network Supervisor", "vatsim" => 11, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "ADM", "type" => "admin", "name_small" => "ADM", "name_long" => "Administrator", "name_grp" => "Network Administrator", "vatsim" => 12, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],

            ["code" => "P1", "type" => "pilot", "name_small" => "P1", "name_long" => "P1", "name_grp" => "Online Pilot", "vatsim" => 1, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "P2", "type" => "pilot", "name_small" => "P2", "name_long" => "P2", "name_grp" => "Flight Fundamentals", "vatsim" => 2, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "P3", "type" => "pilot", "name_small" => "P3", "name_long" => "P3", "name_grp" => "VFR Pilot", "vatsim" => 4, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "P4", "type" => "pilot", "name_small" => "P4", "name_long" => "P4", "name_grp" => "IFR Pilot", "vatsim" => 8, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "P5", "type" => "pilot", "name_small" => "P5", "name_long" => "P5", "name_grp" => "Advanced IFR Pilot", "vatsim" => 16, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "P6", "type" => "pilot", "name_small" => "P6", "name_long" => "P6", "name_grp" => "P6", "vatsim" => 32, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "P7", "type" => "pilot", "name_small" => "P7", "name_long" => "P7", "name_grp" => "P7", "vatsim" => 64, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "P8", "type" => "pilot", "name_small" => "P8", "name_long" => "P8", "name_grp" => "P8", "vatsim" => 128, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["code" => "P9", "type" => "pilot", "name_small" => "P9", "name_long" => "P9", "name_grp" => "Pilot Flight Instructor", "vatsim" => 256, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
        ));

        Schema::create("mship_security", function($table){
            $table->increments("security_id");
            $table->string("name", 25);
            $table->smallInteger("alpha");
            $table->smallInteger("numeric");
            $table->smallInteger("symbols");
            $table->smallInteger("length");
            $table->smallInteger("expiry");
            $table->boolean("optional");
            $table->boolean("default");
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table("mship_security")->insert(array(
            ["name" => "Standard Member Security", "alpha" => 3, "numeric" => 1, "symbols" => 0, "length" => 4, "expiry" => 0, "optional" => 1, "default" => 1, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "Fixed: Level 1", "alpha" => 3, "numeric" => 1, "symbols" => 0, "length" => 4, "expiry" => 45, "optional" => 0, "default" => 0, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "Fixed: Level 2", "alpha" => 4, "numeric" => 2, "symbols" => 0, "length" => 6, "expiry" => 35, "optional" => 0, "default" => 0, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "Fixed: Level 3", "alpha" => 5, "numeric" => 2, "symbols" => 1, "length" => 8, "expiry" => 25, "optional" => 0, "default" => 0, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "Fixed: Level 4", "alpha" => 6, "numeric" => 2, "symbols" => 1, "length" => 10, "expiry" => 15, "optional" => 0, "default" => 0, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
        ));

        Schema::create("mship_note_type", function($table){
            $table->increments("note_type_id")->unsigned();
            $table->string("name", 80);
            $table->boolean("is_available")->default(1);
            $table->boolean("is_system")->default(0);
            $table->enum("colour_code", array("default", "info", "success", "danger", "warning"))->default("info");
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table("mship_note_type")->insert(array(
            ["name" => "System Generated", "is_available" => 0, "is_system" => 1, "colour_code" => "default", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "General", "is_available" => 1, "is_system" => 0, "colour_code" => "info", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
        ));

        Schema::create("mship_account_note", function($table){
            $table->bigIncrements("account_note_id")->unsigned();
            $table->integer("note_type_id")->unsigned();
            $table->integer("account_id")->unsigned();
            $table->integer("writer_id")->unsigned();
            $table->text("content");
            $table->timestamps();
            $table->softDeletes();
        });

        // Creates the roles table
        Schema::create('mship_role', function ($table) {
            $table->increments('role_id')->unsigned();
            $table->string('name', 40);
            $table->boolean("default")->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table("mship_role")->insert(array(
            ["name" => "PrivAcc", "default" => 0, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "Members", "default" => "1", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
        ));

        // Creates the assigned_roles (Many-to-Many relation) table
        Schema::create('mship_account_role', function ($table) {
            $table->increments('account_role_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->timestamps();
        });

        // Creates the permissions table
        Schema::create('mship_permission', function ($table) {
            $table->increments('permission_id')->unsigned();
            $table->string('name');
            $table->string('display_name', 50);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table("mship_permission")->insert(array(
            ["name" => "*", "display_name" => "SUPERMAN POWERS", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],

            ["name" => "adm/dashboard", "display_name" => "Admin / Dashboard ", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/search", "display_name" => "Admin / Search ", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],

            ["name" => "adm/mship", "display_name" => "Admin / Membership ", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],

            ["name" => "adm/mship/account", "display_name" => "Admin / Membership / Account", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*", "display_name" => "Admin / Membership / Account / View", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/list", "display_name" => "Admin / Membership / Account / List", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/datachanges", "display_name" => "Admin / Membership / Account / Data Changes", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/datachanges/view", "display_name" => "Admin / Membership / Account / Data Changes / View", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/flag/view", "display_name" => "Admin / Membership / Account / Flag / View", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/flags", "display_name" => "Admin / Membership / Account / Flag", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/roles", "display_name" => "Admin / Membership / Account / Roles", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/roles/attach", "display_name" => "Admin / Membership / Account / Roles / Attach", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/roles/*/detach", "display_name" => "Admin / Membership / Account / Roles / Detach", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/note/create", "display_name" => "Admin / Membership / Account / Note / Create", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/note/view", "display_name" => "Admin / Membership / Account / Note / View", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/note/filter", "display_name" => "Admin / Membership / Account / Note / View", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/notes", "display_name" => "Admin / Membership / Account / Note", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/receivedEmails", "display_name" => "Admin / Membership / Account / Received Emails", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/security", "display_name" => "Admin / Membership / Account / Security", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/security/change", "display_name" => "Admin / Membership / Account / Security / Change", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/security/enable", "display_name" => "Admin / Membership / Account / Security / Enable", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/security/reset", "display_name" => "Admin / Membership / Account / Security / Reset", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/security/view", "display_name" => "Admin / Membership / Account / Security / View", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/sentEmails", "display_name" => "Admin / Membership / Account / Sent Emails", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/timeline", "display_name" => "Admin / Membership / Account / Timeline", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/view/email", "display_name" => "Admin / Membership / Account / View / Email", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/impersonate", "display_name" => "Admin / Membership / Account / Impersonate", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/own", "display_name" => "Admin / Membership / Account / View & Manage Own", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],

            ["name" => "adm/mship/account/*/bans", "display_name" => "Admin / Membership / Account / Bans", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/ban/add", "display_name" => "Admin / Membership / Account / Ban / Add", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/ban/edit", "display_name" => "Admin / Membership / Account / Ban / Edit", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/ban/view", "display_name" => "Admin / Membership / Account / Ban / View", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/ban/reverse", "display_name" => "Admin / Membership / Account / Ban / Reverse", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],

            ["name" => "adm/mship/permission", "display_name" => "Admin / Membership / Permission", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/permission/create", "display_name" => "Admin / Membership / Permission / Create", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/permission/list", "display_name" => "Admin / Membership / Permission / List", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/permission/*/update", "display_name" => "Admin / Membership / Permission / Update", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/permission/*/delete", "display_name" => "Admin / Membership / Permission / Delete", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/permission/*/delete", "display_name" => "Admin / Membership / Permission / Delete", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/permission/attach", "display_name" => "Admin / Membership / Permission / Attach", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],

            ["name" => "adm/mship/role", "display_name" => "Admin / Membership / Role", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/role/*/delete", "display_name" => "Admin / Membership / Role / Delete", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/role/*/update", "display_name" => "Admin / Membership / Role / Update", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/role/create", "display_name" => "Admin / Membership / Role / Create", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/role/list", "display_name" => "Admin / Membership / Role / List", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/role/default", "display_name" => "Admin / Membership / Roles / Set Default", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],

            ["name" => "adm/sys/timeline/mship", "display_name" => "Admin / System / Timeline / Membership", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],

            ["name" => "teamspeak/serveradmin", "display_name" => "TeamSpeak / Server Admin", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "teamspeak/idle/extended", "display_name" => "TeamSpeak / Extended Idle", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "teamspeak/idle/permanent", "display_name" => "TeamSpeak / Permanent Idle", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
        ));

        // Creates the permission_role (Many-to-Many relation) table
        Schema::create('mship_permission_role', function ($table) {
            $table->increments('permission_role_id')->unsigned();
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->timestamps();
        });

        DB::table("mship_permission_role")->insert(array(
            ["role_id" => 1, "permission_id" => 1, "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
        ));

        Schema::create('mship_account_ban', function(Blueprint $table) {
            $table->increments('account_ban_id');
            $table->mediumInteger('account_id')->unsigned();
            $table->mediumInteger('banned_by')->unsigned();
            $table->smallInteger('type')->unsigned();
            $table->integer('reason_id')->unsigned()->nullable();
            $table->text('reason_extra');
            $table->smallInteger('period_amount')->unsigned();
            $table->enum('period_unit', array('M', 'H', 'D'));
            $table->timestamp('period_start');
            $table->timestamp('period_finish')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mship_ban_reason', function(Blueprint $table) {
            $table->increments('ban_reason_id');
            $table->string('name', 40);
            $table->text('reason_text');
            $table->smallInteger('period_amount')->unsigned();
            $table->enum('period_unit', array('M', 'H', 'D'));
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('mship_account_ban', function(Blueprint $table) {
            $table->foreign('reason_id')->references('ban_reason_id')->on('mship_ban_reason')
                  ->onDelete('restrict')
                  ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mship_account');
        Schema::dropIfExists('mship_account_ban');
        Schema::dropIfExists('mship_account_email');
        Schema::dropIfExists('mship_account_note');
        Schema::dropIfExists('mship_account_qualification');
        Schema::dropIfExists('mship_account_role');
        Schema::dropIfExists('mship_account_security');
        Schema::dropIfExists('mship_account_state');
        Schema::dropIfExists('mship_ban_reason');
        Schema::dropIfExists('mship_note_type');
        Schema::dropIfExists('mship_permission');
        Schema::dropIfExists('mship_permission_role');
        Schema::dropIfExists('mship_qualification');
        Schema::dropIfExists('mship_role');
        Schema::dropIfExists('mship_security');
    }
}
