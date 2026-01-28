<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roles = [
            'Gatwick GND Students',
            'ATC Mentor (Gatwick GND)',
        ];

        foreach ($roles as $roleName) {
            $roleId = DB::table('mship_role')
                ->where('name', $roleName)
                ->value('id');

            DB::table('mship_account_role')
                ->where('role_id', $roleId)
                ->where('model_type', 'App\\Models\\Mship\\Account')
                ->delete();

            DB::table('mship_role_permission')
                ->where('role_id', $roleId)
                ->delete();

            DB::table('mship_role')
                ->where('id', $roleId)
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
