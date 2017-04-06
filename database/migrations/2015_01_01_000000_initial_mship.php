<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InitialMship extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mship_account', function ($table) {
            $table->integer('account_id')->unsigned()->primary();
            $table->string('slack_id', 10)->unique()->nullable();
            $table->string('name_first', 50);
            $table->string('name_last', 50);
            $table->string('session_id')->default('');
            $table->timestamp('last_login')->nullable();
            $table->bigInteger('last_login_ip')->unsigned()->default(0);
            $table->string('remember_token', 100)->nullable();
            $table->enum('gender', ['M', 'F'])->nullable();
            $table->enum('experience', ['N', 'A', 'P', 'B'])->default('N');
            $table->smallInteger('age')->unsigned()->default(0);
            $table->smallInteger('status')->unsigned()->default(0);
            $table->boolean('is_invisible')->default(0);
            $table->boolean('debug')->default(false);
            $table->string('template');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->timestamp('cert_checked_at')->nullable();
            $table->softDeletes();
        });

        DB::table('mship_account')->insert([
            ['account_id' => env('SYSTEM_ACCOUNT_VATUK', 707070), 'name_first' => 'VATSIM', 'name_last' => 'UK', 'status' => App\Models\Mship\Account::STATUS_SYSTEM],
            ['account_id' => env('SYSTEM_ACCOUNT_VATSIM', 606060), 'name_first' => 'VATSIM', 'name_last' => 'NET', 'status' => App\Models\Mship\Account::STATUS_SYSTEM],
        ]);

        Schema::create('mship_account_email', function ($table) {
            $table->bigIncrements('account_email_id')->unsigned();
            $table->string('email');
            $table->integer('account_id')->unsigned();
            $table->boolean('is_primary')->default(0);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('mship_account_email')->insert([
            ['account_id' => env('SYSTEM_ACCOUNT_VATUK', 707070), 'email' => 'no-reply@vatsim-uk.co.uk', 'is_primary' => true, 'verified_at' => Carbon::now()->toDateTimeString()],
            ['account_id' => env('SYSTEM_ACCOUNT_VATSIM', 606060), 'email' => 'no-reply@vatsim.net', 'is_primary' => true, 'verified_at' => Carbon::now()->toDateTimeString()],
        ]);

        Schema::create('mship_account_qualification', function ($table) {
            $table->bigIncrements('account_qualification_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->integer('qualification_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mship_account_security', function ($table) {
            $table->bigIncrements('account_security_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->integer('security_id');
            $table->string('value', 60);
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('mship_account_state', function ($table) {
            $table->bigIncrements('account_state_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->tinyInteger('state');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mship_qualification', function ($table) {
            $table->increments('qualification_id');
            $table->string('code', 3)->unique();
            $table->enum('type', ['atc', 'pilot', 'training_atc', 'training_pilot', 'admin']);
            $table->string('name_small', 15);
            $table->string('name_long', 25);
            $table->string('name_grp', 40);
            $table->smallInteger('vatsim');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['type', 'vatsim']);
        });

        DB::table('mship_qualification')->insert([
            ['code' => 'OBS', 'type' => 'atc', 'name_small' => 'OBS', 'name_long' => 'Observer', 'name_grp' => 'Observer', 'vatsim' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['code' => 'S1', 'type' => 'atc', 'name_small' => 'STU', 'name_long' => 'Student 1', 'name_grp' => 'Ground Controller', 'vatsim' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['code' => 'S2', 'type' => 'atc', 'name_small' => 'STU2', 'name_long' => 'Student 2', 'name_grp' => 'Tower Controller', 'vatsim' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['code' => 'S3', 'type' => 'atc', 'name_small' => 'STU+', 'name_long' => 'Student 3', 'name_grp' => 'Approach Controller', 'vatsim' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['code' => 'C1', 'type' => 'atc', 'name_small' => 'CTR', 'name_long' => 'Controller 1', 'name_grp' => 'Area Controller', 'vatsim' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['code' => 'C2', 'type' => 'atc', 'name_small' => 'CTR+', 'name_long' => 'Senior Controller', 'name_grp' => 'Senior Controller', 'vatsim' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['code' => 'C3', 'type' => 'atc', 'name_small' => 'CTR+', 'name_long' => 'Senior Controller', 'name_grp' => 'Senior Controller', 'vatsim' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            ['code' => 'I1', 'type' => 'training_atc', 'name_small' => 'INS', 'name_long' => 'Instructor', 'name_grp' => 'Instructor', 'vatsim' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['code' => 'I2', 'type' => 'training_atc', 'name_small' => 'INS+', 'name_long' => 'Senior Instructor', 'name_grp' => 'Senior Instructor', 'vatsim' => 9, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['code' => 'I3', 'type' => 'training_atc', 'name_small' => 'INS+', 'name_long' => 'Senior Instructor', 'name_grp' => 'Senior Instructor', 'vatsim' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            ['code' => 'SUP', 'type' => 'admin', 'name_small' => 'SUP', 'name_long' => 'Supervisor', 'name_grp' => 'Network Supervisor', 'vatsim' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['code' => 'ADM', 'type' => 'admin', 'name_small' => 'ADM', 'name_long' => 'Administrator', 'name_grp' => 'Network Administrator', 'vatsim' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            ['code' => 'P1', 'type' => 'pilot', 'name_small' => 'P1', 'name_long' => 'P1', 'name_grp' => 'Online Pilot', 'vatsim' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['code' => 'P2', 'type' => 'pilot', 'name_small' => 'P2', 'name_long' => 'P2', 'name_grp' => 'Flight Fundamentals', 'vatsim' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['code' => 'P3', 'type' => 'pilot', 'name_small' => 'P3', 'name_long' => 'P3', 'name_grp' => 'VFR Pilot', 'vatsim' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['code' => 'P4', 'type' => 'pilot', 'name_small' => 'P4', 'name_long' => 'P4', 'name_grp' => 'IFR Pilot', 'vatsim' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        Schema::create('mship_security', function ($table) {
            $table->increments('security_id');
            $table->string('name', 25);
            $table->smallInteger('alpha');
            $table->smallInteger('numeric');
            $table->smallInteger('symbols');
            $table->smallInteger('length');
            $table->smallInteger('expiry');
            $table->boolean('optional');
            $table->boolean('default');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('mship_security')->insert([
            ['name' => 'Standard Member Security', 'alpha' => 3, 'numeric' => 1, 'symbols' => 0, 'length' => 4, 'expiry' => 0, 'optional' => 1, 'default' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Fixed: Level 1', 'alpha' => 3, 'numeric' => 1, 'symbols' => 0, 'length' => 4, 'expiry' => 45, 'optional' => 0, 'default' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Fixed: Level 2', 'alpha' => 4, 'numeric' => 2, 'symbols' => 0, 'length' => 6, 'expiry' => 35, 'optional' => 0, 'default' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Fixed: Level 3', 'alpha' => 5, 'numeric' => 2, 'symbols' => 1, 'length' => 8, 'expiry' => 25, 'optional' => 0, 'default' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Fixed: Level 4', 'alpha' => 6, 'numeric' => 2, 'symbols' => 1, 'length' => 10, 'expiry' => 15, 'optional' => 0, 'default' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        Schema::create('mship_note_type', function ($table) {
            $table->increments('note_type_id')->unsigned();
            $table->string('name', 80);
            $table->string('short_code', 20)->nullable();
            $table->boolean('is_available')->default(1);
            $table->boolean('is_system')->default(0);
            $table->boolean('is_default')->default(0);
            $table->enum('colour_code', ['default', 'info', 'success', 'danger', 'warning'])->default('info');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('mship_note_type')->insert([
            ['name' => 'System Generated', 'short_code' => 'default', 'is_available' => 0, 'is_system' => 1, 'is_default' => 1, 'colour_code' => 'default', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'General', 'short_code' => '', 'is_available' => 1, 'is_system' => 0, 'is_default' => 0, 'colour_code' => 'info', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Discipline', 'short_code' => 'discipline', 'is_available' => 1, 'is_system' => 1, 'is_default' => 0, 'colour_code' => 'danger', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

        ]);

        Schema::create('mship_account_note', function ($table) {
            $table->bigIncrements('account_note_id')->unsigned();
            $table->integer('note_type_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->integer('writer_id')->unsigned();
            $table->integer('attachment_id')->unsigned();
            $table->string('attachment_type', 255);
            $table->text('content');
            $table->timestamps();
            $table->softDeletes();
        });

        // Creates the roles table
        Schema::create('mship_role', function ($table) {
            $table->increments('role_id')->unsigned();
            $table->string('name', 40);
            $table->boolean('default')->default(0);
            $table->smallInteger('session_timeout')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('mship_role')->insert([
            ['name' => 'PrivAcc', 'default' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Members', 'default' => '1', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

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
            $table->string('display_name', 100);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('mship_permission')->insert([
            ['name' => '*', 'display_name' => 'SUPERMAN POWERS', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            ['name' => 'adm/dashboard', 'display_name' => 'Admin / Dashboard ', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/search', 'display_name' => 'Admin / Search ', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            ['name' => 'adm/mship', 'display_name' => 'Admin / Membership ', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            ['name' => 'adm/mship/account', 'display_name' => 'Admin / Membership / Account', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*', 'display_name' => 'Admin / Membership / Account / View', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/list', 'display_name' => 'Admin / Membership / Account / List', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/datachanges', 'display_name' => 'Admin / Membership / Account / Data Changes', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/datachanges/view', 'display_name' => 'Admin / Membership / Account / Data Changes / View', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/flag/view', 'display_name' => 'Admin / Membership / Account / Flag / View', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/flags', 'display_name' => 'Admin / Membership / Account / Flag', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/roles', 'display_name' => 'Admin / Membership / Account / Roles', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/roles/attach', 'display_name' => 'Admin / Membership / Account / Roles / Attach', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/roles/*/detach', 'display_name' => 'Admin / Membership / Account / Roles / Detach', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/note/create', 'display_name' => 'Admin / Membership / Account / Note / Create', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/note/view', 'display_name' => 'Admin / Membership / Account / Note / View', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/note/filter', 'display_name' => 'Admin / Membership / Account / Note / View', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/notes', 'display_name' => 'Admin / Membership / Account / Note', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/receivedEmails', 'display_name' => 'Admin / Membership / Account / Received Emails', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/security', 'display_name' => 'Admin / Membership / Account / Security', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/security/change', 'display_name' => 'Admin / Membership / Account / Security / Change', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/security/enable', 'display_name' => 'Admin / Membership / Account / Security / Enable', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/security/reset', 'display_name' => 'Admin / Membership / Account / Security / Reset', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/security/view', 'display_name' => 'Admin / Membership / Account / Security / View', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/sentEmails', 'display_name' => 'Admin / Membership / Account / Sent Emails', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/timeline', 'display_name' => 'Admin / Membership / Account / Timeline', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/view/email', 'display_name' => 'Admin / Membership / Account / View / Email', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/impersonate', 'display_name' => 'Admin / Membership / Account / Impersonate', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/own', 'display_name' => 'Admin / Membership / Account / View & Manage Own', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            ['name' => 'adm/mship/account/*/bans', 'display_name' => 'Admin / Membership / Account / Bans', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/ban/add', 'display_name' => 'Admin / Membership / Account / Ban / Add', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/ban/modify', 'display_name' => 'Admin / Membership / Account / Ban / Modify', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/*/ban/view', 'display_name' => 'Admin / Membership / Account / Ban / View', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/ban/*/repeal', 'display_name' => 'Admin / Membership / Account / Ban / Repeal', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            ['name' => 'adm/mship/permission', 'display_name' => 'Admin / Membership / Permission', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/permission/create', 'display_name' => 'Admin / Membership / Permission / Create', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/permission/list', 'display_name' => 'Admin / Membership / Permission / List', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/permission/*/update', 'display_name' => 'Admin / Membership / Permission / Update', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/permission/*/delete', 'display_name' => 'Admin / Membership / Permission / Delete', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/permission/*/delete', 'display_name' => 'Admin / Membership / Permission / Delete', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/permission/attach', 'display_name' => 'Admin / Membership / Permission / Attach', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            ['name' => 'adm/mship/role', 'display_name' => 'Admin / Membership / Role', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/role/*/delete', 'display_name' => 'Admin / Membership / Role / Delete', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/role/*/update', 'display_name' => 'Admin / Membership / Role / Update', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/role/create', 'display_name' => 'Admin / Membership / Role / Create', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/role/list', 'display_name' => 'Admin / Membership / Role / List', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/role/default', 'display_name' => 'Admin / Membership / Roles / Set Default', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            ['name' => 'adm/sys/timeline/mship', 'display_name' => 'Admin / System / Timeline / Membership', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            ['name' => 'teamspeak/serveradmin', 'display_name' => 'TeamSpeak / Server Admin', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/idle/extended', 'display_name' => 'TeamSpeak / Extended Idle', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/idle/permanent', 'display_name' => 'TeamSpeak / Permanent Idle', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // Creates the permission_role (Many-to-Many relation) table
        Schema::create('mship_permission_role', function ($table) {
            $table->increments('permission_role_id')->unsigned();
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->timestamps();
        });

        DB::table('mship_permission_role')->insert([
            ['role_id' => 1, 'permission_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        Schema::create('mship_account_ban', function (Blueprint $table) {
            $table->increments('account_ban_id');
            $table->mediumInteger('account_id')->unsigned();
            $table->mediumInteger('banned_by')->unsigned();
            $table->smallInteger('type')->unsigned();
            $table->integer('reason_id')->unsigned()->nullable();
            $table->text('reason_extra');
            $table->timestamp('period_start');
            $table->timestamp('period_finish')->nullable();
            $table->timestamps();
            $table->timestamp('repealed_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('mship_ban_reason', function (Blueprint $table) {
            $table->increments('ban_reason_id');
            $table->string('name', 40);
            $table->text('reason_text');
            $table->smallInteger('period_amount')->unsigned();
            $table->enum('period_unit', ['M', 'H', 'D']);
            $table->timestamps();
            $table->softDeletes();
        });
//
//        Schema::table('mship_account_ban', function(Blueprint $table) {
//            $table->foreign('reason_id')->references('ban_reason_id')->on('mship_ban_reason')
//                  ->onDelete('restrict')
//                  ->onUpdate('restrict');
//        });
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
