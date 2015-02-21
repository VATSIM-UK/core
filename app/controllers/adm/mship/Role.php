<?php

namespace Controllers\Adm\Mship;

use \AuthException;
use \Auth;
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
        $roles = RoleData::orderBy("name", "ASC")->get();

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
        $role = new RoleData(Input::all());
        if(!$role->save()){
            return Redirect::route("adm.mship.role.create")->withErrors($role->errors());
        }

        if(count(Input::get("permissions")) > 0 && Auth::admin()->get()->hasPermission("adm/mship/permission/assign")){
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

    public function postUpdate(RoleData $role){
        if(!$role OR !$role->exists){
            return Redirect::route("adm.mship.role.index")->withError("Role doesn't exist!");
        }

        // Let's create!
        $role = $role->fill(Input::all());
        if(!$role->save()){
            return Redirect::route("adm.mship.role.update")->withErrors($role->errors());
        }

        if(Auth::admin()->get()->hasPermission("adm/mship/permission/assign")){
            // Detatch permissions!
            foreach($role->permissions as $p){
                if(!in_array($p->permission_id, Input::get("permissions", []))){
                    $role->detachPermission($p);
                }
            }

            // Attach all permissions.
            $role->attachPermissions(Input::get("permissions", []));
        }

        return Redirect::route("adm.mship.role.index")->withSuccess("Role '".$role->name."' has been updated - don't forget to set the permissions properly!");
    }

    public function anyDelete(RoleData $role){
        if(!$role OR !$role->exists){
            return Redirect::route("adm.mship.role.index")->withError("Role doesn't exist!");
        }

        // Is it the default role?
        if($role->default){
            return Redirect::route("adm.mship.role.index")->withError("You cannot delete the default role.");
        }

        // Let's delete!
        $role->delete();
        return Redirect::route("adm.mship.role.index")->withSuccess("Role, associated permissions and membership entries were all deleted.");
    }
}
