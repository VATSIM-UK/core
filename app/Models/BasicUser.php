<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

/**
 * App\Models\BasicUser
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BasicUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BasicUser wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BasicUser whereUsername($value)
 * @mixin \Eloquent
 */
class BasicUser extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $table = 'basic_users';
    public $timestamps = false;
}
