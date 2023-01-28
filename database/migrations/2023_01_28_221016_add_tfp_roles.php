<?php

use Spatie\Permission\Models\Permission;
use Illuminate\Database\Migrations\Migration;
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
        $studentRole = Role::create([
            'name' => "TFP Student",
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentPermission = Permission::create(['name' => "discord/pilot/student/tfp", 'guard_name' => 'web']);

        $studentRole->givePermissionTo($studentPermission);
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
