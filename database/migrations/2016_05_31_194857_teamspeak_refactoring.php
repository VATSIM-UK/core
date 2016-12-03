<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TeamspeakRefactoring extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teamspeak_registration', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::create('teamspeak_channel', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary();
            $table->unsignedSmallInteger('parent_id')->nullable();
            $table->string('name', 30);
            $table->boolean('protected')->nullable();
        });

        Schema::create('teamspeak_group', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('dbid');
            $table->string('name', 30);
            $table->string('type', 1)->default('s');
            $table->boolean('default')->default(0);
            $table->boolean('protected');
            $table->unsignedInteger('permission_id')->nullable();
            $table->unsignedInteger('qualification_id')->nullable();
        });

        Schema::create('teamspeak_channel_group_permission', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('channel_id')->unique();
            $table->unsignedSmallInteger('channelgroup_id');
            $table->unsignedInteger('permission_id');
        });

        DB::table('mship_permission')
            ->where('name', 'teamspeak/serveradmin')
            ->update(['name' => 'teamspeak/servergroup/serveradmin', 'display_name' => 'TeamSpeak / Server Group / Server Admin']);
        DB::table('mship_permission')->insert([
            ['name' => 'teamspeak/servergroup/divisionstaff', 'display_name' => 'TeamSpeak / Server Group / Division Staff', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/servergroup/webstaff', 'display_name' => 'TeamSpeak / Server Group / Web Staff', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/servergroup/rtsm', 'display_name' => 'TeamSpeak / Server Group / RTS Manager', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/servergroup/leadmentor', 'display_name' => 'TeamSpeak / Server Group / Lead Mentor', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/servergroup/atcstaff', 'display_name' => 'TeamSpeak / Server Group / ATC Staff', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/servergroup/ptdstaff', 'display_name' => 'TeamSpeak / Server Group / PTD Staff', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/servergroup/member', 'display_name' => 'TeamSpeak / Server Group / Member', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/channel/essex', 'display_name' => 'TeamSpeak / Channel / Essex', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/channel/heathrow', 'display_name' => 'TeamSpeak / Channel / Heathrow', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/channel/egtt', 'display_name' => 'TeamSpeak / Channel / London', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/channel/northern', 'display_name' => 'TeamSpeak / Channel / Northern', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/channel/scottish', 'display_name' => 'TeamSpeak / Channel / Scottish', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/channel/serts', 'display_name' => 'TeamSpeak / Channel / South East', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/channel/swrts', 'display_name' => 'TeamSpeak / Channel / South West', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/channel/military', 'display_name' => 'TeamSpeak / Channel / Military', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'teamspeak/channel/pilot', 'display_name' => 'TeamSpeak / Channel / Pilot Training', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        try {
            Schema::table('teamspeak_log', function (Blueprint $table) {
                $table->dropForeign('teamspeak_log_registration_id_foreign');
            });
        } catch (Exception $e) {
            // Do nothing with a missing FK.
        }
        Schema::drop('teamspeak_log');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('teamspeak_log', function ($table) {
            $table->increments('id')->unsigned();
            $table->integer('registration_id')->unsigned()->nullable();
            $table->string('type', 75);
            $table->timestamps();
        });
        Schema::table('teamspeak_log', function ($table) {
            $table->foreign('registration_id')->references('id')->on('teamspeak_registration');
        });

        DB::table('mship_permission')
            ->where('name', 'teamspeak/servergroup/serveradmin')
            ->update(['name' => 'teamspeak/serveradmin', 'display_name' => 'TeamSpeak / Server Admin']);
        DB::table('mship_permission')
            ->where('name', 'LIKE', 'teamspeak/channel/%')
            ->orWhere('name', 'LIKE', 'teamspeak/servergroup/%')
            ->delete();

        Schema::drop('teamspeak_group');
        Schema::drop('teamspeak_channel_group_permission');
        Schema::drop('teamspeak_channel');

        Schema::table('teamspeak_registration', function (Blueprint $table) {
            $table->enum('status', ['new', 'active', 'deleted'])->after('dbid');
        });
    }
}
