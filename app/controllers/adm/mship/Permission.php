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

class Permission extends \Controllers\Adm\AdmController {

    public function getIndex() {
        // ORM it all!
        $permissions = PermissionData::orderBy("name", "ASC")->paginate(20);

        return $this->viewMake("adm.mship.permission.index")
                    ->with("permissions", $permissions);
    }

    public function getCreate() {
        $roles = RoleData::orderBy("name", "ASC")->get();

        return $this->viewMake("adm.mship.permision.create_or_update")
                    ->with("roles", $roles);
    }

    public function postCreate(){
        // Let's create!
        $permission = new PermissionData();
        $permission->name = Input::get("name");
        $permission->display_name = Input::get("display_name");
        $permission->save();

        if(count(Input::get("roles")) > 0){
            $permission->attachRoles(Input::get("roles"));
        }

        return Redirect::route("adm.mship.permission.index")->withSuccess("Permission '".$permission->display_name."' has been created - don't forget to attach it to some roles!");
    }

    public function getUpdate(PermissionData $permission) {
        if(!$permission OR !$permission->exists){
            return Redirect::route("adm.mship.permissions.index")->withError("Permission doesn't exist!");
        }

        $roles = RoleData::orderBy("name", "ASC")->get();

        return $this->viewMake("adm.mship.permissions.create_or_update")
                    ->with("permission", $permission)
                    ->with("roles", $roles);
    }
}
