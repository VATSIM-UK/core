<?php

namespace App\Models\TeamSpeak;

use App\Scopes\TeamSpeak\GroupScope;

/**
 * App\Models\TeamSpeak\ChannelGroup.
 *
 * @property-read \App\Models\Mship\Permission $permission
 * @property-read \App\Models\Mship\Qualification $qualification
 * @mixin \Eloquent
 * @property int $id
 * @property int $dbid
 * @property string $name
 * @property string $type
 * @property bool $default
 * @property bool $protected
 * @property int $permission_id
 * @property int $qualification_id
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ChannelGroup whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ChannelGroup whereDbid($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ChannelGroup whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ChannelGroup whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ChannelGroup whereDefault($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ChannelGroup whereProtected($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ChannelGroup wherePermissionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ChannelGroup whereQualificationId($value)
 */
class ChannelGroup extends Group
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GroupScope);
    }
}
