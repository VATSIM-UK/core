<?php

namespace App\Http\Controllers\Adm\Mship;

use Input;
use Redirect;
use App\Models\Mship\Role as RoleData;
use App\Models\Mship\Permission as PermissionData;

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
        $permission = new PermissionData(Input::all());
        if (!$permission->save()) {
            return Redirect::route('adm.mship.permission.create')->withErrors($permission->errors());
        }

        if (count(Input::get('roles')) > 0 && $this->account->hasPermission('adm/mship/permission/attach')) {
            $permission->attachRoles(Input::get('roles'));
        }

        return Redirect::route('adm.mship.permission.index')->withSuccess("Permission '".$permission->display_name."' has been created - don't forget to attach it to some roles!");
    }

    public function getUpdate(PermissionData $permission)
    {
        if (!$permission or !$permission->exists) {
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
        if (!$permission or !$permission->exists) {
            return Redirect::route('adm.mship.permissions.index')->withError("Permission doesn't exist!");
        }

        // Let's create!
        $permission = $permission->fill(Input::all());
        if (!$permission->save()) {
            return Redirect::route('adm.mship.permission.update')->withErrors($permission->errors());
        }

        if ($this->account->hasPermission('adm/mship/permission/attach')) {
            // Detatch permissions!
            foreach ($permission->roles as $r) {
                if (!in_array($r->id, Input::get('roles', []))) {
                    $permission->detachRole($r);
                }
            }

            // Attach all permissions.
            $permission->attachRoles(Input::get('roles', []));
        }

        return Redirect::route('adm.mship.permission.index')->withSuccess("Permission '".$permission->display_name."' has been updated - don't forget to set the roles properly!");
    }

    public function anyDelete(PermissionData $permission)
    {
        if (!$permission or !$permission->exists) {
            return Redirect::route('adm.mship.permissions.index')->withError("Permission doesn't exist!");
        }

        // Let's delete!
        $permission->delete();

        return Redirect::route('adm.mship.permission.index')->withSuccess('Permission and associated roles deleted.');
    }
}
