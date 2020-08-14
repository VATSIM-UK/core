<?php

use Illuminate\Database\Migrations\Migration;

class CreateNovaFeedbackPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createPermission('feedback');
        $this->createPermission('feedback/submitter');
        $this->createPermission('feedback/action');
        $this->createPermission('feedback/own');
        $this->createPermission('feedback/view/atc');
        $this->createPermission('feedback/view/pilot');
        $this->createPermission('feedback/view/group');
        $this->createPermission('feedback/view/atcmentor');
        $this->createPermission('feedback/view/eve');
        $this->createPermission('feedback/view/live');

        $this->deletePermission('adm/mship/feedback');
        $this->deletePermission('adm/mship/feedback/list');
        $this->deletePermission('adm/mship/feedback/list/atc');
        $this->deletePermission('adm/mship/feedback/list/pilot');
        $this->deletePermission('adm/mship/feedback/view/*');
        $this->deletePermission('adm/mship/feedback/configure/*');
        $this->deletePermission('adm/mship/feedback/view/*/action');
        $this->deletePermission('adm/mship/feedback/view/*/unaction');
        $this->deletePermission('adm/mship/feedback/view/*/reporter');
        $this->deletePermission('adm/mship/feedback/list/*');
        $this->deletePermission('adm/mship/feedback/list/group');
        $this->deletePermission('adm/mship/feedback/view/group');
        $this->deletePermission('adm/mship/account/*/feedback');
        $this->deletePermission('adm/mship/feedback/list/atcmentor');
        $this->deletePermission('adm/mship/feedback/configure/atcmentor');
        $this->deletePermission('adm/mship/feedback/list/*');
        $this->deletePermission('adm/mship/feedback/list/*/export');
        $this->deletePermission('adm/mship/account/*/feedback');
        $this->deletePermission('adm/mship/feedback/new');
        $this->deletePermission('adm/mship/feedback/toggle');
        $this->deletePermission('adm/mship/feedback/configure/eve/*');
        $this->deletePermission('adm/mship/feedback/configure/liv/*');
        $this->deletePermission('adm/mship/feedback/new');
        $this->deletePermission('adm/mship/feedback/view/*/send');
        $this->deletePermission('adm/mship/feedback/view/atc/send');
        $this->deletePermission('adm/mship/feedback/view/atcmentor/send');
        $this->deletePermission('adm/mship/feedback/view/own/');
    }

    private function createPermission(string $name, $guard = 'web')
    {
        return \DB::table(config('permission.table_names.permissions'))->insert([
            'name' => $name,
            'guard_name' => $guard,
        ]);
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
