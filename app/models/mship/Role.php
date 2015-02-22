<?php

namespace Models\Mship;

use Watson\Validating\ValidatingTrait;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use \Models\Mship\Permission as PermissionData;

class Role extends \Models\aModel {

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
            $def = Role::isDefault()->where("role_id", "!=", $model->getKey())->get();
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
            $def = Role::isDefault()->where("role_id", "!=", $model->getKey())->get();
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
        return $this->belongsToMany("\Models\Mship\Account", "mship_account_role");
    }

    public function permissions(){
        return $this->belongsToMany("\Models\Mship\Permission", "mship_permission_role");
    }

    public function hasPermission(PermissionData $permission) {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        return $this->permissions->contains($permission);
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
