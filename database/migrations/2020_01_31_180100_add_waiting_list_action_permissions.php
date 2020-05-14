<?php

use Illuminate\Database\Migrations\Migration;

class AddWaitingListActionPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createPermission('waitingLists/addAccounts');
        $this->createPermission('waitingLists/addFlags');
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
        return \Spatie\Permission\Models\Permission::create([
            'name' => $name,
            'guard_name' => $guard,
        ]);
    }
}
