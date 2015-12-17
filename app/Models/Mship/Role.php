<?php

namespace App\Models\Mship;

use Watson\Validating\ValidatingTrait;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use App\Models\Mship\Permission as PermissionData;

class Role extends \App\Models\aModel {

    use SoftDeletingTrait, ValidatingTrait;

    protected $table = "mship_role";
    protected $primaryKey = "role_id";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['name', 'default'];
    protected $attributes = ['default' => 0];
    protected $rules = [
        'name' => 'required|between:4,40',
        'default' => 'required|boolean',
    ];

    public static function eventDeleted($model) {
        parent::eventCreated($model);

        // Since we've deleted a role, let's delete all related accounts and permissions!
        foreach($model->accounts as $a){
            $model->accounts()->detach($a);
        }

        $model->detachPermissions($model->permissions);
    }

    public static function eventCreated($model) {
        parent::eventCreated($model);

        // Let's undefault any other default models.
        if($model->default){
            $def = Role::isDefault()->where("role_id", "!=", $model->getKey())->first();
            if($def){
                $def->default = 0;
                $def->save();
            }
        }
    }

    public static function eventUpdated($model) {
        parent::eventUpdated($model);

        // Let's undefault any other default models.
        if($model->default){
            $def = Role::isDefault()->where("role_id", "!=", $model->getKey())->first();
            if($def){
                $def->default = 0;
                $def->save();
            }
        }
    }

    public static function scopeIsDefault($query){
        return $query->whereDefault(1);
    }

    public function accounts(){
        return $this->belongsToMany("\App\Models\Mship\Account", "mship_account_role")->withTimestamps();
    }

    public function permissions(){
        return $this->belongsToMany("\App\Models\Mship\Permission", "mship_permission_role")->withTimestamps();
    }

    public function hasPermission($permission) {
        if (is_object($permission) OR is_numeric($permission)) {
            return $this->permissions->contains($permission);
        }

        // It's a string, let's be a bit more creative.
        return !$this->permissions->filter(function($perm) use($permission){
                return strcasecmp($perm->name, $permission) == 0 OR $perm->name == "*";
        })->isEmpty();
    }

    public function attachPermission(PermissionData $permission){
        if($this->permissions->contains($permission->getKey())){
            return false;
        }

        return $this->permissions()->attach($permission);
    }

    public function attachPermissions($permissions){
        foreach($permissions as $p){
            if($p instanceof PermissionData){
                $this->attachPermission($p);
            } elseif(is_numeric($p) && $p = PermissionData::find($p)){
                $this->attachPermission($p);
            }
        }
    }

    public function detachPermission(PermissionData $permission){
        if(!$this->permissions->contains($permission->getKey())){
            return false;
        }

        return $this->permissions()->detach($permission);
    }

    public function detachPermissions($permissions){
        foreach($permissions as $p){
            if($p instanceof PermissionData){
                $this->detachPermission($p);
            } elseif(is_numeric($p) && $p = PermissionData::find($p)){
                $this->detachPermission($p);
            }
        }
    }

}
