<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VisitingTransferInitialSetup extends Migration
{
    public function up()
    {
        //
        Schema::create('vt_application', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('type')->default(\App\Modules\Visittransfer\Models\Application::TYPE_VISIT);
            $table->integer('account_id')->unsigned();
            $table->integer('facility_id')->unsigned()->nullable();
            $table->text('statement')->nullable();
            $table->smallInteger('status')->default(\App\Modules\Visittransfer\Models\Application::STATUS_IN_PROGRESS);
            $table->text('status_note');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vt_reference', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('application_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->string('email', 85)->nullable();
            $table->string('relationship', 85)->nullable();
            $table->text('reference')->nullable();
            $table->smallInteger('status')->default(\App\Modules\Visittransfer\Models\Reference::STATUS_DRAFT);
            $table->text('status_note');
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('reminded_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('vt_facility', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 60);
            $table->string('description', 500);
            $table->boolean('training_required')->default(0);
            $table->enum('training_team', ['atc', 'pilot']);
            $table->mediumInteger('training_spaces')->unsigned()->default(0);
            $table->boolean('stage_statement_enabled')->default(1);
            $table->boolean('stage_reference_enabled')->default(1);
            $table->smallInteger('stage_reference_quantity')->default(1);
            $table->boolean('stage_checks')->default(1);
            $table->boolean('auto_acceptance')->default(0);
            $table->boolean('open')->default(0);
            $table->softDeletes();
        });

        DB::table('mship_note_type')->insert([
            ['name' => 'Visiting &amp; Transfer', 'short_code' => 'visittransfer', 'is_available' => 1, 'is_system' => 1, 'is_default' => 0, 'colour_code' => 'info', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

        ]);

        DB::table('mship_permission')->insert([
            ['name' => 'adm/visit-transfer', 'display_name' => 'Admin / Visit &amp; Transfer', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/dashboard', 'display_name' => 'Admin / Visit &amp; Transfer / Dashboard', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/facility', 'display_name' => 'Admin / Visit &amp; Transfer / Facility', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/create', 'display_name' => 'Admin / Visit &amp; Transfer / Facility / Create', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/*/update', 'display_name' => 'Admin / Visit &amp; Transfer / Facility / Update', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/application', 'display_name' => 'Admin / Visit &amp; Transfer / Applications', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/application/open', 'display_name' => 'Admin / Visit &amp; Transfer / Applications (Open)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/application/closed', 'display_name' => 'Admin / Visit &amp; Transfer / Applications (Closed)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/application/review', 'display_name' => 'Admin / Visit &amp; Transfer / Applications (Under Review)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/application/accepted', 'display_name' => 'Admin / Visit &amp; Transfer / Applications (Accepted)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/application/completed', 'display_name' => 'Admin / Visit &amp; Transfer / Applications (Completed)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/application/*', 'display_name' => 'Admin / Visit &amp; Transfer / Applications / View', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/application/*/accept', 'display_name' => 'Admin / Visit &amp; Transfer / Applications / Accept', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/application/*/reject', 'display_name' => 'Admin / Visit &amp; Transfer / Applications / Reject', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/reference', 'display_name' => 'Admin / Visit &amp; Transfer / References', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/reference/pending-submission', 'display_name' => 'Admin / Visit &amp; Transfer / References (Pending Submission)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/reference/submitted', 'display_name' => 'Admin / Visit &amp; Transfer / References (Submitted)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/reference/under-review', 'display_name' => 'Admin / Visit &amp; Transfer / References (Under Review)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/reference/accepted', 'display_name' => 'Admin / Visit &amp; Transfer / References (Accepted)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/reference/rejected', 'display_name' => 'Admin / Visit &amp; Transfer / References (Rejected)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/reference/*', 'display_name' => 'Admin / Visit &amp; Transfer / References / View', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/reference/*/accept', 'display_name' => 'Admin / Visit &amp; Transfer / References / Accept', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/visit-transfer/reference/*/reject', 'display_name' => 'Admin / Visit &amp; Transfer / References / Reject', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }

    public function down()
    {
        $tables = [
            'vt_application',
            'vt_reference',
            'vt_facility',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        DB::table('mship_permission')->where('name', 'LIKE', 'adm/visit-transfer%')->delete();
    }
}
