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

        // Creates the permission_role (Many-to-Many relation) table
        Schema::create('mship_permission_role', function ($table) {
            $table->increments('permission_role_id')->unsigned();
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->timestamps();
        });
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
