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
        // Clear cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        foreach (['TWR' => 'S2', 'APP' => 'S3', 'ENR' => 'C1'] as $type => $qualification) {
            $studentRole = Role::create([
                'name' => "ATC Students ($type)",
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $lowercaseQualification = strtolower($qualification);

            $studentPermission = Permission::create(['name' => "discord/atc/student/{$lowercaseQualification}", 'guard_name' => 'web']);

            $studentRole->givePermissionTo($studentPermission);
        }
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
