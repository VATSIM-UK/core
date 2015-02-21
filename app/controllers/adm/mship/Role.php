<?php

namespace Controllers\Adm\Mship;

use \AuthException;
use \Input;
use \Session;
use \Response;
use \Request;
use \URL;
use \View;
use \VatsimSSO;
use \Config;
use \Redirect;
use \DB;
use \Models\Mship\Role as RoleData;
use \Models\Mship\Permission as PermissionData;

class Role extends \Controllers\Adm\AdmController {

    public function getIndex() {
        // ORM it all!
        $roles = RoleData::orderBy("name", "ASC")->paginate(50);

        return $this->viewMake("adm.mship.role.index")
                    ->with("roles", $roles);
    }

    public function getCreate() {
        $permissions = PermissionData::orderBy("name", "ASC")->get();

        return $this->viewMake("adm.mship.role.create_or_update")
                    ->with("permissions", $permissions);
    }

    public function postCreate(){
        // Let's create!
        $role = new RoleData();
        $role->name = Input::get("name");
        $role->save();

        if(count(Input::get("permissions")) > 0){
            $role->attachPermissions(Input::get("permissions"));
        }

        return Redirect::route("adm.mship.role.index")->withSuccess("Role '".$role->name."' has been created - don't forget to set the permissions properly!");
    }

    public function getUpdate(RoleData $role) {
        if(!$role OR !$role->exists){
            return Redirect::route("adm.mship.role.index")->withError("Role doesn't exist!");
        }

        $permissions = PermissionData::orderBy("name", "ASC")->get();

        return $this->viewMake("adm.mship.role.create_or_update")
                    ->with("role", $role)
                    ->with("permissions", $permissions);
    }
}
