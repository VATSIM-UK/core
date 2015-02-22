<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MShipRolesPermissions extends Migration {

    /**
     * Run the migrations.
     * @return void
     */
    public function up() {
        // Creates the roles table
        Schema::create('mship_role', function ($table) {
            $table->increments('role_id')->unsigned();
            $table->string('name', 40);
            $table->boolean("default")->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table("mship_role")->insert(array(
            ["name" => "Super Administrator", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
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

            ["name" => "adm/mship", "display_name" => "Admin / Membership ", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],

            ["name" => "adm/mship/account", "display_name" => "Admin / Membership / Account", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*", "display_name" => "Admin / Membership / Account / View", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/list", "display_name" => "Admin / Membership / Account / List", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/datachanges", "display_name" => "Admin / Membership / Account / Data Changes", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/datachanges/view", "display_name" => "Admin / Membership / Account / Data Changes / View", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/flag/view", "display_name" => "Admin / Membership / Account / Flag / View", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/flags", "display_name" => "Admin / Membership / Account / Flag", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/note/create", "display_name" => "Admin / Membership / Account / Note / Create", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "adm/mship/account/*/note/view", "display_name" => "Admin / Membership / Account / Note / View", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
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

            ["name" => "adm/sys/timeline/mship", "display_name" => "Admin / System / Timeline / Membership", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
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
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down() {
        Schema::drop('mship_account_role');
        Schema::drop('mship_permission_role');
        Schema::drop('mship_role');
        Schema::drop('mship_permission');
    }

}
