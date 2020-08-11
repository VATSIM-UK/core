<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddDiscordPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createPermission('discord/member');
        $this->createPermission('discord/dsg');
        $this->createPermission('discord/web');
        $this->createPermission('discord/moderator');
        $this->createPermission('discord/memberservices');
        $this->createPermission('discord/marketing');
        $this->createPermission('discord/trainingmanager');
        $this->createPermission('discord/atc/divisioninstructor');
        $this->createPermission('discord/atc/appinstructor');
        $this->createPermission('discord/atc/twrinstructor');
        $this->createPermission('discord/atc/ncinstructor');
        $this->createPermission('discord/atc/examiner');
        $this->createPermission('discord/atc/mentor/s1');
        $this->createPermission('discord/atc/mentor/s2');
        $this->createPermission('discord/atc/mentor/s3');
        $this->createPermission('discord/atc/mentor/c1');
        $this->createPermission('discord/atc/mentor/heathrow');
        $this->createPermission('discord/pilot/examiner');
        $this->createPermission('discord/pilot/instructor');
        $this->createPermission('discord/pilot/mentor');
        $this->createPermission('discord/graphics');
        $this->createPermission('discord/rostering');
        $this->createPermission('discord/livestreaming');

        $discordMember = Permission::findByName('discord/member');
        $member = Role::findByName('Member');
        $member->givePermissionTo($discordMember);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    private function createPermission(string $name, $guard = 'web')
    {
        return \DB::table(config('permission.table_names.permissions'))->insert([
            'name' => $name,
            'guard_name' => $guard,
        ]);
    }
}
