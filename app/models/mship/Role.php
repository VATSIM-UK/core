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
    public static $relationsData = [
        "accounts" => array(self::BELONGS_TO_MANY, "\Models\Mship\Account", "table" => "mship_account_role"),
        "permissions" => array(self::BELONGS_TO_MANY, "\Models\Mship\Permission", "table" => "mship_permission_role"),
    ];
    public $autoHydrateEntityFromInput = true;    // hydrates on new entries' validation
    public $forceEntityHydrationFromInput = true; // hydrates whenever validation is called

    public function hasPermission($permission) {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        return $this->perms->contains($permission);
    }

}
