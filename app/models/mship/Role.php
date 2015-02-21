<?php

namespace Models\Mship;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

use \Models\Mship\Permission as PermissionData;

class Role extends \Models\aModel {

    use SoftDeletingTrait;

    protected $table = "mship_role";
    protected $primaryKey = "role_id";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public static $rules = [
        'name' => 'required|between:4,40',
    ];

    public function accounts(){
        return $this->belongsToMany("\Models\Mship\Account", "mship_account_role");
    }

    public function permissions(){
        return $this->belongsToMany("\Models\Mship\Permission", "mship_permission_role");
    }

    public function hasPermission($permission) {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        return $this->perms->contains($permission);
    }

}
