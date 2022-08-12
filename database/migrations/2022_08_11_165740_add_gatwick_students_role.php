<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddGatwickStudentsRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Clear cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        Role::create([
            'name' => 'Gatwick Students',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Permission::create(['name' => 'discord/gatwick-students', 'guard_name' => 'web']);
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
}
