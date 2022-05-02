<?php

namespace App\Http\Controllers\Adm\Mship;

use Illuminate\Support\Facades\Request;
use Redirect;
use Spatie\Permission\Models\Permission as PermissionData;
use Spatie\Permission\Models\Role as RoleData;

class Permission extends \App\Http\Controllers\Adm\AdmController
{
    public function getIndex()
    {
        // ORM it all!
        $permissions = PermissionData::orderBy('name', 'ASC')
            ->with('roles')
            ->paginate(20);

        return $this->viewMake('adm.mship.permission.index')
            ->with('permissions', $permissions);
    }

    public function getCreate()
    {
        $roles = RoleData::orderBy('name', 'ASC')->get();

        return $this->viewMake('adm.mship.permission.create_or_update')
            ->with('roles', $roles);
    }

    public function postCreate()
    {
        // Let's create!
        $permission = new PermissionData(Request::only('name', 'guard_name'));
        if (! $permission->save()) {
            return Redirect::route('adm.mship.permission.create')->withErrors($permission->errors());
        }

        if (! is_null(Request::input('roles')) && $this->account->can('use-permission', 'adm/mship/permission/attach')) {
            $permission->syncRoles(Request::input('roles'));
        }

        return Redirect::route('adm.mship.permission.index')->withSuccess("Permission '".$permission->name."' has been created - don't forget to attach it to some roles!");
    }

    public function getUpdate(PermissionData $permission)
    {
        if (! $permission or ! $permission->exists) {
            return Redirect::route('adm.mship.permissions.index')->withError("Permission doesn't exist!");
        }

        $permission->load('roles');

        $roles = RoleData::orderBy('name', 'ASC')
            ->get();

        return $this->viewMake('adm.mship.permission.create_or_update')
            ->with('permission', $permission)
            ->with('roles', $roles);
    }

    public function postUpdate(PermissionData $permission)
    {
        if (! $permission or ! $permission->exists) {
            return Redirect::route('adm.mship.permissions.index')->withError("Permission doesn't exist!");
        }

        if ($this->account->can('use-permission', 'adm/mship/permission/attach')) {
            // Detatch permissions!
            foreach ($permission->roles as $r) {
                if (! in_array($r->id, Request::input('roles', []))) {
                    $permission->removeRole($r);
                }
            }

            // Attach all permissions.
            $permission->assignRole(Request::input('roles', []));
        }

        return Redirect::route('adm.mship.permission.index')->withSuccess("Permission '".$permission->name."' has been updated - don't forget to set the roles properly!");
    }

    public function anyDelete(PermissionData $permission)
    {
        if (! $permission or ! $permission->exists) {
            return Redirect::route('adm.mship.permissions.index')->withError("Permission doesn't exist!");
        }

        // Let's delete!
        $permission->delete();

        return Redirect::route('adm.mship.permission.index')->withSuccess('Permission and associated roles deleted.');
    }
}
