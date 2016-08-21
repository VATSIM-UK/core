<?php

namespace App\Models\TeamSpeak;

use App\Scopes\TeamSpeak\GroupScope;

/**
 * App\Models\TeamSpeak\ServerGroup
 *
 * @mixin \Eloquent
 * @property integer $id
 * @property integer $dbid
 * @property string $name
 * @property string $type
 * @property boolean $default
 * @property boolean $protected
 * @property integer $permission_id
 * @property integer $qualification_id
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ServerGroup whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ServerGroup whereDbid($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ServerGroup whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ServerGroup whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ServerGroup whereDefault($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ServerGroup whereProtected($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ServerGroup wherePermissionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ServerGroup whereQualificationId($value)
 */
class ServerGroup extends Group
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GroupScope);
    }
}
