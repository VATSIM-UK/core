<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VisitingTransferInitialSetup extends Migration
{

    public function up()
    {
        //
        Schema::create('vt_application', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger("type")->default(\App\Modules\Visittransfer\Models\Application::TYPE_VISIT);
            $table->integer('account_id')->unsigned();
            $table->integer('facility_id')->unsigned()->nullable();
            $table->text('statement')->nullable();
//            $table->boolean('approved')->nullable();
//            $table->integer('reason_id')->unsigned()->nullable();
//            $table->integer('reviewed_by')->unsigned()->nullable();
//            $table->timestamp('reapplication_date')->nullable();
            $table->smallInteger("status")->default(\App\Modules\Visittransfer\Models\Application::STATUS_IN_PROGRESS);
            $table->timestamp("submitted_at")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vt_reference', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('application_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->string("email", 85)->nullable();
            $table->string("relationship", 85)->nullable();
            $table->text('reference')->nullable();
            $table->smallInteger("status")->default(\App\Modules\Visittransfer\Models\Reference::STATUS_DRAFT);
            $table->timestamp("submitted_at")->nullable();
            $table->softDeletes();
        });

        Schema::create('vt_facility', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 30);
            $table->string("description", 500);
            $table->boolean("training_required")->default(0);
            $table->mediumInteger('training_spaces')->unsigned()->default(0);
            $table->boolean("stage_statement_enabled")->default(1);
            $table->boolean("stage_reference_enabled")->default(1);
            $table->smallInteger("stage_reference_quantity")->default(1);
            $table->boolean("stage_checks")->default(1);
            $table->boolean("auto_acceptance")->default(0);
            $table->boolean("open")->default(0);
            $table->softDeletes();
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

    public function down()
    {
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