<?php

namespace App\Http\Controllers\Adm\Mship\Account;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account as AccountData;
use Illuminate\Support\Facades\Request;
use Redirect;
use Spatie\Permission\Models\Role as RoleData;

class Roles extends AdmController
{
    public function getRoleDetach(AccountData $mshipAccount, RoleData $role)
    {
        if (! $mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        if (! $role) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'roles'])
                ->withError('The selected role does not exist.');
        }

        if (! $mshipAccount->roles->contains($role->id)) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'roles'])
                ->withError('This role is not attached to this user.');
        }

        // Let's remove!
        $mshipAccount->removeRole($role);

        return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'roles'])
            ->withSuccess($role->name.' role detached successfully. This user lost '.count($role->permissions).' permissions.');
    }

    public function postRoleAttach(AccountData $mshipAccount)
    {
        if (! $mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        // Let's try and load this RoleData
        $role = RoleData::find(Request::input('role'));

        if (! $role) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'roles'])
                ->withError('The selected role does not exist.');
        }

        // Let's add!
        if (! $mshipAccount->roles->contains($role->id)) {
            $mshipAccount->assignRole($role);
        }

        return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'roles'])
            ->withSuccess($role->name.' role attached successfully. This user inherited '.count($role->permissions).' permissions.');
    }
}
