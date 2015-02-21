<?php

namespace Models\Mship;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Permission extends \Models\aModel {
    use SoftDeletingTrait;

    protected $table = "mship_permission";
    protected $primaryKey = "permission_id";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    public static $rules = [
        'name' => 'required',
        'display_name' => 'required|between:3,50',
    ];
    public static $relationsData = [
        "roles" => array(self::BELONGS_TO_MANY, "\Models\Mship\Role", "table" => "mship_permission_role")
    ];
    public $autoHydrateEntityFromInput = true;    // hydrates on new entries' validation
    public $forceEntityHydrationFromInput = true; // hydrates whenever validation is called

    public function scopeIsName($query, $name){
        return $query->whereName($name);
    }
}
