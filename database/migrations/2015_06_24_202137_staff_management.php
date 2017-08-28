<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class StaffManagement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id')->unsigned();
            $table->string('name', 50);
            $table->timestamps();
        });

        Schema::create('staff_positions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->nullable();
            $table->string('type', 1);
            $table->string('name', 50);
            $table->timestamps();
        });

        Schema::create('staff_account_position', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->unsigned();
            $table->integer('position_id')->unsigned();
            $table->timestamps();
        });

        Schema::create('staff_attribute_position', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('attribute_id')->unsigned();
            $table->integer('position_id')->unsigned();
            $table->timestamps();
        });

        Schema::create('staff_services', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->timestamps();
        });

        Schema::table('staff_services', function ($table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staff_services', function ($table) {
            //
        });

        Schema::drop('staff_attributes');
        Schema::drop('staff_positions');
        Schema::drop('staff_account_position');
        Schema::drop('staff_attribute_position');
        Schema::drop('staff_services');
    }
}
