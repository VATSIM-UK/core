<?php

use Illuminate\Database\Migrations\Migration;

class RemoveUnusedPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->deletePermission('waitingLists/atc/addAccounts');
        $this->deletePermission('waitingLists/atc/addFlags');
        $this->deletePermission('waitingLists/atc/delete');
        $this->deletePermission('waitingLists/atc/removeAccount');
        $this->deletePermission('waitingLists/pilot/addAccounts');
        $this->deletePermission('waitingLists/pilot/addFlags');
        $this->deletePermission('waitingLists/pilot/delete');
        $this->deletePermission('waitingLists/pilot/removeAccount');
    }

    private function deletePermission(string $name)
    {
        try {
            $permission = \Spatie\Permission\Models\Permission::findByName($name);
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            return false;
        }

        return \Spatie\Permission\Models\Permission::destroy($permission->id);
    }
}
