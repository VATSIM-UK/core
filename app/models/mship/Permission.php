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

    public function scopeIsName($query, $name){
        return $query->whereName($name);
    }

    public function roles(){
        return $this->belongsToMany("\Models\Mship\Role", "mship_permission_role");
    }
}
