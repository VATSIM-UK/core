<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $p1MentorRole = Role::create([
            'name' => 'P1 Mentor',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $p2MentorRole = Role::create([
            'name' => 'P2 Mentor',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $p1MentorPermission = Permission::create(['name' => 'discord/pilot/mentor/p1', 'guard_name' => 'web']);
        $p2MentorPermission = Permission::create(['name' => 'discord/pilot/mentor/p2', 'guard_name' => 'web']);

        $p1MentorRole->givePermissionTo($p1MentorPermission);
        $p2MentorRole->givePermissionTo($p2MentorPermission);
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
};
