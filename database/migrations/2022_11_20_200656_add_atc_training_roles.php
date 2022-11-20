<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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

        foreach (["S2", "S3", "C1"] as $qualification) {
            $studentRole = Role::create([
                'name' => "$qualification Students",
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $mentorRole = Role::create([
                'name' => "$qualification Mentors",
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $lowercaseQualification = strtolower($qualification);

            $studentPermission = Permission::create(['name' => "discord/{$lowercaseQualification}-students", 'guard_name' => 'web']);
            $mentorPermission = Permission::create(['name' => "discord/{$lowercaseQualification}-mentors", 'guard_name' => 'web']);

            $studentRole->givePermissionTo($studentPermission);
            $mentorRole->givePermissionTo($mentorPermission);
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
