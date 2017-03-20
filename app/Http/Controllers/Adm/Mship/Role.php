<?php

namespace App\Http\Controllers\Adm\Mship;

use Input;
use Redirect;
use App\Models\Mship\Role as RoleData;
use App\Models\Mship\Permission as PermissionData;

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
        // Let's create!
        if (!$this->account->hasPermission('adm/mship/role/default')) {
            $data = Input::except('default');
        } else {
            $data = Input::all();
        }
        $role = new RoleData($data);
        if (!$role->save()) {
            return Redirect::route('adm.mship.role.create')->withErrors($role->errors());
        }

        if (count(Input::get('permissions')) > 0 && $this->account->hasPermission('adm/mship/permission/attach')) {
            $role->attachPermissions(Input::get('permissions'));
        }

        return Redirect::route('adm.mship.role.index')->withSuccess("Role '".$role->name."' has been created - don't forget to set the permissions properly!");
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

        $role->load('permissions');

        // Let's create!
        if (!$this->account->hasPermission('adm/mship/role/default')) {
            $data = Input::except('default');
        } else {
            $data = Input::all();
        }
        $role = $role->fill($data);
        if (!$role->save()) {
            return Redirect::route('adm.mship.role.update')->withErrors($role->errors());
        }

        if ($this->account->hasPermission('adm/mship/permission/attach')) {
            // Detatch permissions!
            foreach ($role->permissions as $p) {
                if (!in_array($p->id, Input::get('permissions', []))) {
                    $role->detachPermission($p);
                }
            }

            // Attach all permissions.
            $role->attachPermissions(Input::get('permissions', []));
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
