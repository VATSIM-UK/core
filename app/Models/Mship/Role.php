<?php

namespace App\Models\Mship;

use App\Models\Model;
use App\Models\Mship\Permission as PermissionData;

/**
 * App\Models\Mship\Role
 *
 * @property int $id
 * @property string $name
 * @property int $default
 * @property int|null $session_timeout
 * @property int $password_mandatory
 * @property int $password_lifetime
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account[] $accounts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read bool $is_default
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Permission[] $permissions
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Role hasTimeout()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Role isDefault()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Role whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Role wherePasswordLifetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Role wherePasswordMandatory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Role whereSessionTimeout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Role whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Role extends Model
{
    protected $table = 'mship_role';
    protected $primaryKey = 'id';
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = ['name', 'default', 'password_mandatory', 'session_timeout', 'password_lifetime'];
    protected $attributes = ['default' => 0];
    protected $rules = [
        'name' => 'required|between:4,40',
        'default' => 'required|boolean',
    ];
    protected $trackedEvents = ['created', 'updated', 'deleted'];

    protected static function boot()
    {
        parent::boot();

        self::created([get_called_class(), 'eventCreated']);
        self::updated([get_called_class(), 'eventUpdated']);
    }

    public static function eventCreated($model)
    {
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

            if (strcasecmp($perm->name, $permission) == 0 || strcasecmp($stripped, $permission) == 0 || $perm->name == '*') {
                return true;
            }

            // Secondary fallback - Mainly for non-numeric slugs
            // Add slashes
            $perm_has = preg_quote($perm->name, '/');
            // Replace wildcard
            $perm_has = str_replace('\*', '[A-Za-z0-9]+', $perm_has);
            $perm_has = '/^'.$perm_has.'$/';

            return (bool) preg_match($perm_has, $permission);
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
