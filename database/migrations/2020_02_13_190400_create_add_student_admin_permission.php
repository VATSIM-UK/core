<?php

use Illuminate\Database\Migrations\Migration;

class CreateAddStudentAdminPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createPermission('waitingLists/addAccountsAdmin');
    }

    private function createPermission(string $name, $guard = 'web')
    {
        return \Spatie\Permission\Models\Permission::create([
            'name' => $name,
            'guard_name' => $guard,
        ]);
    }
}
