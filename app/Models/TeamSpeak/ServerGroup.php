<?php

namespace App\Models\TeamSpeak;

use App\Scopes\TeamSpeak\GroupScope;

/**
 * App\Models\TeamSpeak\ServerGroup.
 *
 * @property int $id
 * @property int $dbid
 * @property string $name
 * @property string $type
 * @property int $default
 * @property int $protected
 * @property int|null $permission_id
 * @property int|null $qualification_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read \App\Models\Mship\Permission|null $permission
 * @property-read \App\Models\Mship\Qualification|null $qualification
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\ServerGroup whereDbid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\ServerGroup whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\ServerGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\ServerGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\ServerGroup wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\ServerGroup whereProtected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\ServerGroup whereQualificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\ServerGroup whereType($value)
 *
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
