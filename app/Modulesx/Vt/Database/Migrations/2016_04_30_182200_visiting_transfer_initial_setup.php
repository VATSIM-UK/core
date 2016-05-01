<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VisitingTransferInitialSetup extends Migration {

    public function up(){
        //
        Schema::create('vt_application', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger("type")->default(\App\Modules\Vt\Application::$TYPE_VISIT);
            $table->integer('account_id')->unsigned();
            $table->integer('facility_id')->unsigned()->nullable();
            $table->text('statement')->nullable();
            $table->boolean('submitted_statement')->default(DB::raw('FALSE'));
            $table->boolean('submitted_referees')->default(DB::raw('FALSE'));
            $table->boolean('submitted_application')->default(DB::raw('FALSE'));
//            $table->boolean('approved')->nullable();
//            $table->integer('reason_id')->unsigned()->nullable();
//            $table->integer('reviewed_by')->unsigned()->nullable();
//            $table->timestamp('reapplication_date')->nullable();
            $table->timestamps();
        });

        Schema::create('vt_referee', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('application_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->text('reference')->nullable();
            $table->smallInteger("status")->default(\App\Modules\Vt\Referee::$STATUS_DRAFT);
            $table->timestamp("submitted_at")->nullable();
        });

        Schema::create('vt_facility', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 15);
            $table->boolean("is_training")->default(0);
            $table->mediumInteger('gnd_spaces')->unsigned()->default(0);
            $table->mediumInteger('twr_spaces')->unsigned()->default(0);
            $table->mediumInteger('app_spaces')->unsigned()->default(0);
            $table->mediumInteger('ctr_spaces')->unsigned()->default(0);
        });
        //--POSSIBLY NOT NEEDED BELOW HERE //
//
//        Schema::create('vt_stage', function (Blueprint $table) {
//            $table->mediumIncrements('id');
//            $table->string('key', 50)->unique();
//            $table->string('name', 50);
//            $table->text('description');
//        });
//
//        Schema::create('vt_stage_skip', function (Blueprint $table) {
//            $table->increments('id');
//            $table->mediumInteger('stage_id')->unsigned();
//            $table->integer('facility_id')->unsigned()->nullable();
//            $table->integer('application_id')->unsigned()->nullable();
//            $table->string('reason', 100);
//        });
//
//        Schema::create('vt_check', function (Blueprint $table) {
//            $table->mediumIncrements('id');
//            $table->string('name', 50);
//            $table->text('description');
//        });
//
//        Schema::create('vt_application_check', function (Blueprint $table) {
//            $table->increments('id');
//            $table->integer('application_id')->unsigned();
//            $table->mediumInteger('check_id')->unsigned();
//            $table->text('notes');
//            $table->boolean('status');
//        });
//
//        Schema::create('vt_reason', function (Blueprint $table) {
//            $table->increments('id');
//            $table->text('description');
//            $table->mediumInteger('default_timelimit');
//        });
    }

    public function down(){
        //
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

        $tables = [
            'vt_application',
            'vt_referee',
            'vt_type',
            'vt_facility',
            'vt_stage',
            'vt_stage_skip',
            'vt_check',
            'vt_application_check',
            'vt_reason'
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    }

}