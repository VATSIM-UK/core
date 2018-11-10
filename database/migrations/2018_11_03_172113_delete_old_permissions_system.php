<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DeleteOldPermissionsSystem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('mship_account_role');
        Schema::dropIfExists('mship_permission_role');
        Schema::dropIfExists('mship_role');
        Schema::dropIfExists('mship_permission');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('mship_role', function ($table) {
            $table->increments('id')->unsigned();
            $table->string('name', 75);
            $table->boolean('default')->default(0);
            $table->smallInteger('session_timeout')->unsigned()->nullable();
            $table->boolean('password_mandatory')->default(0);
            $table->integer('password_lifetime')->default(0);
            $table->timestamps();
        });

        Schema::create('mship_permission', function ($table) {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('display_name', 100);
            $table->timestamps();
        });

        Schema::create('mship_account_role', function ($table) {
            $table->increments('id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->timestamps();
        });

        Schema::create('mship_permission_role', function ($table) {
            $table->increments('id')->unsigned();
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('mship_permission_role', function ($table) {
            $table->foreign('permission_id')->references('id')->on('mship_permission')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('mship_role')->onDelete('cascade');
        });

        Schema::table('mship_account_role', function ($table) {
            $table->foreign('account_id')->references('id')->on('mship_account');
            $table->foreign('role_id')->references('id')->on('mship_role')->onDelete('cascade');
        });
    }
}
