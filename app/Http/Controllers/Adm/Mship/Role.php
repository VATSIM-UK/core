<?php

namespace App\Http\Controllers\Adm\Mship;

use Spatie\Permission\Models\Permission as PermissionData;
use Spatie\Permission\Models\Role as RoleData;
use Input;
use Redirect;

class Role extends \App\Http\Controllers\Adm\AdmController
{
    public function getIndex()
    {
        // ORM it all!
        $roles = RoleData::orderBy('name', 'ASC')
            ->with('permissions')
            ->get();

        return $this->viewMake('adm.mship.role.index')
            ->with('roles', $roles);
    }

    public function getCreate()
    {
        $permissions = PermissionData::orderBy('name', 'ASC')->get();

        return $this->viewMake('adm.mship.role.create_or_update')
            ->with('permissions', $permissions);
    }

    public function postCreate()
    {
        $data = Input::only('name', 'guard_name', 'password_mandatory', 'password_lifetime', 'session_timeout', 'default');

        $role = new RoleData($data);
        $role->save();

        if (!is_null(Input::get('permissions')) && $this->account->can('use-permission', 'adm/mship/role/attach')) {
            $role->syncPermissions(Input::get('permissions'));
        }

        return Redirect::route('adm.mship.role.index')->withSuccess("Role '".$role->name."' has been created - don't forget to attach it to some roles!");
    }

    public function getUpdate(RoleData $role)
    {
        if (!$role or !$role->exists) {
            return Redirect::route('adm.mship.role.index')->withError("Role doesn't exist!");
        }

        $permissions = PermissionData::orderBy('name', 'ASC')
            ->get();

        return $this->viewMake('adm.mship.role.create_or_update')
            ->with('role', $role)
            ->with('permissions', $permissions);
    }

    public function postUpdate(RoleData $role)
    {
        if (!$role or !$role->exists) {
            return Redirect::route('adm.mship.role.index')->withError("Role doesn't exist!");
        }


        $data = Input::only('name', 'guard_name', 'password_mandatory', 'password_lifetime', 'session_timeout', 'default');

        $role = $role->fill($data);
        $role->save();

        foreach ($role->permissions as $p) {
            if (!in_array($p->id, Input::get('permissions', []))) {
                $role->revokePermissionTo($p);
            }
        }

        if (!is_null(Input::get('permissions')) && $this->account->can('use-permission', 'adm/mship/role/attach')) {
            $role->syncPermissions(Input::get('permissions'));
        }

        return Redirect::route('adm.mship.role.index')->withSuccess("Role '".$role->name."' has been updated - don't forget to set the permissions properly!");
    }

    public function anyDelete(RoleData $role)
    {
        if (!$role or !$role->exists) {
            return Redirect::route('adm.mship.role.index')->withError("Role doesn't exist!");
        }

        // Is it the default role?
        if ($role->default) {
            return Redirect::route('adm.mship.role.index')->withError('You cannot delete the default role.');
        }

        // Let's delete!
        $role->delete();

        return Redirect::route('adm.mship.role.index')->withSuccess('Role, associated permissions and membership entries were all deleted.');
    }
}
