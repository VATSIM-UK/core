<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
//
//        Schema::table('staff_attributes', function ($table) {
//            $table->foreign('service_id')->references('id')->on('staff_services');
//        });
//
//        Schema::table('staff_positions', function ($table) {
//            $table->foreign('parent_id')->references('id')->on('staff_positions');
//        });
//
//        Schema::table('staff_account_position', function ($table) {
//            $table->foreign('account_id')->references('account_id')->on('mship_account');
//            $table->foreign('position_id')->references('id')->on('staff_positions');
//        });
//
//        Schema::table('staff_attribute_position', function ($table) {
//            $table->foreign('attribute_id')->references('id')->on('staff_attributes');
//            $table->foreign('position_id')->references('id')->on('staff_positions');
//        });
//
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
        //        Schema::table('staff_attributes', function ($table) {
//            $table->dropForeign('staff_attributes_service_id_foreign');
//        });
//
//        Schema::table('staff_positions', function ($table) {
//            $table->dropForeign('staff_positions_parent_id_foreign');
//        });
//
//        Schema::table('staff_account_position', function ($table) {
//            $table->dropForeign('staff_account_position_account_id_foreign');
//            $table->dropForeign('staff_account_position_position_id_foreign');
//        });
//
//        Schema::table('staff_attribute_position', function ($table) {
//            $table->dropForeign('staff_attribute_position_attribute_id_foreign');
//            $table->dropForeign('staff_attribute_position_position_id_foreign');
//        });

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
