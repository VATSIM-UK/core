<?php

namespace App\Models\TeamSpeak;

use App\Scopes\TeamSpeak\GroupScope;

/**
 * App\Models\TeamSpeak\ServerGroup
 *
 * @property int $id
 * @property int $dbid
 * @property string $name
 * @property string $type
 * @property bool $default
 * @property bool $protected
 * @property int $permission_id
 * @property int $qualification_id
 * @property-read \App\Models\Mship\Permission $permission
 * @property-read \App\Models\Mship\Qualification $qualification
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ServerGroup whereDbid($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ServerGroup whereDefault($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ServerGroup whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ServerGroup whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ServerGroup wherePermissionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ServerGroup whereProtected($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ServerGroup whereQualificationId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ServerGroup whereType($value)
 * @mixin \Eloquent
 */
class ServerGroup extends Group
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GroupScope);
    }
}
