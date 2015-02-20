<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class EntrustSetupTables extends Migration {

    /**
     * Run the migrations.
     * @return void
     */
    public function up() {
        // Creates the roles table
        Schema::create('mship_role', function ($table) {
            $table->increments('role_id')->unsigned();
            $table->string('name');
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
            $table->softDeletes();

            $table->foreign('account_id')->references('account_id')->on('mship_account')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('role_id')->references('role_id')->on('mship_role');
        });

        // Creates the permissions table
        Schema::create('mship_permission', function ($table) {
            $table->increments('permission_id')->unsigned();
            $table->string('name');
            $table->string('display_name');
            $table->timestamps();
            $table->softDeletes();
        });

        // Creates the permission_role (Many-to-Many relation) table
        Schema::create('mship_permission_role', function ($table) {
            $table->increments('permission_role_id')->unsigned();
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('permission_id')->references('permission_id')->on('mship_permission');
            $table->foreign('role_id')->references('role_id')->on('mship_role');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down() {
        Schema::table('mship_account_role', function (Blueprint $table) {
            $table->dropForeign('mship_account_role_account_id_foreign');
            $table->dropForeign('mship_account_role_role_id_foreign');
        });
        Schema::table('mship_permission_role', function (Blueprint $table) {
            $table->dropForeign('mship_permission_role_permission_id_foreign');
            $table->dropForeign('mship_permission_role_role_id_foreign');
        });
        Schema::drop('mship_account_role');
        Schema::drop('mship_permission_role');
        Schema::drop('mship_role');
        Schema::drop('mship_permission');
    }

}
