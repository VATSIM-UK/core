<?php

namespace App\Models\Mship;

use App\Traits\RecordsActivity;
use App\Models\Mship\Permission as PermissionData;

/**
 * App\Models\Mship\Role
 *
 * @property int $id
 * @property string $name
 * @property bool $default
 * @property int $session_timeout
 * @property bool $password_mandatory
 * @property int $password_lifetime
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account[] $accounts
 * @property-read bool $is_default
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Permission[] $permissions
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Role hasTimeout()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Role isDefault()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Role whereDefault($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Role whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Role whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Role wherePasswordLifetime($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Role wherePasswordMandatory($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Role whereSessionTimeout($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Role whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Role extends \App\Models\Model
{
    use RecordsActivity;

    protected $table = 'mship_role';
    protected $primaryKey = 'id';
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = ['name', 'default'];
    protected $attributes = ['default' => 0];
    protected $rules = [
        'name' => 'required|between:4,40',
        'default' => 'required|boolean',
    ];

    public static function eventDeleted($model)
    {
        parent::eventCreated($model);

        // Since we've deleted a role, let's delete all related accounts and permissions!
        foreach ($model->accounts as $a) {
            $model->accounts()->detach($a);
        }

        $model->detachPermissions($model->permissions);
    }

    public static function eventCreated($model)
    {
        parent::eventCreated($model);

        // Let's undefault any other default models.
        if ($model->default) {
            $def = self::isDefault()->where('id', '!=', $model->getKey())->first();
            if ($def) {
                $def->default = 0;
                $def->save();
            }
        }
    }

    public static function eventUpdated($model)
    {
        parent::eventUpdated($model);

        // Let's undefault any other default models.
        if ($model->default) {
            $def = self::isDefault()->where('id', '!=', $model->getKey())->first();
            if ($def) {
                $def->default = 0;
                $def->save();
            }
        }
    }

    /**
     * Find the default role.
     *
     * @return \Illuminate\Database\Eloquent\Model|mixed|null|static
     */
    public static function findDefault()
    {
        return self::isDefault()->first();
    }

    /**
     * Find the default role or throw an exception.
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     * @exception \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findDefaultOrFail()
    {
        return self::isDefault()->firstOrFail();
    }

    public static function scopeIsDefault($query)
    {
        return $query->whereDefault(1);
    }

    public static function scopeHasTimeout($query)
    {
        return $query->whereNotNull('session_timeout')->where('session_timeout', '!=', 0);
    }

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'mship_account_role')->withTimestamps();
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'mship_permission_role')->withTimestamps();
    }

    public function hasPermission($permission)
    {
        if (is_object($permission) or is_numeric($permission)) {
            return $this->permissions->contains($permission);
        }

        // It's a string, let's be a bit more creative.
        return !$this->permissions->filter(function ($perm) use ($permission) {
            $stripped = preg_replace("/[^A-Za-z0-9\/]/i", '', $perm->name);

            return strcasecmp($perm->name, $permission) == 0 || strcasecmp($stripped, $permission) == 0 || $perm->name == '*';
        })->isEmpty();
    }

    public function attachPermission(PermissionData $permission)
    {
        if ($this->permissions->contains($permission->getKey())) {
            return false;
        }

        return $this->permissions()->attach($permission);
    }

    public function attachPermissions($permissions)
    {
        foreach ($permissions as $p) {
            if ($p instanceof PermissionData) {
                $this->attachPermission($p);
            } elseif (is_numeric($p) && $p = PermissionData::find($p)) {
                $this->attachPermission($p);
            }
        }
    }

    public function detachPermission(PermissionData $permission)
    {
        if (!$this->permissions->contains($permission->getKey())) {
            return false;
        }

        return $this->permissions()->detach($permission);
    }

    public function detachPermissions($permissions)
    {
        foreach ($permissions as $p) {
            if ($p instanceof PermissionData) {
                $this->detachPermission($p);
            } elseif (is_numeric($p) && $p = PermissionData::find($p)) {
                $this->detachPermission($p);
            }
        }
    }

    /**
     * Determine if this role has a password lifetime set.
     *
     * @return bool
     */
    public function hasPasswordLifetime()
    {
        return $this->password_lifetime !== null && $this->password_lifetime !== 0;
    }

    /**
     * Is the password mandatory on this account?
     *
     * @return bool
     */
    public function hasMandatoryPassword()
    {
        return $this->password_mandatory === true || $this->password_mandatory == 1;
    }

    /**
     * Determine if role is default.
     *
     * @return bool
     */
    public function getIsDefaultAttribute()
    {
        return $this->default === 1;
    }

    /**
     * Determine if the current role has a timeout.
     *
     * @return bool
     */
    public function hasSessionTimeout()
    {
        return $this->session_timeout !== null && $this->session_timeout !== 0;
    }
}
