<?php

namespace App\Models\TeamSpeak;

use App\Scopes\TeamSpeak\GroupScope;

/**
 * App\Models\TeamSpeak\ChannelGroup
 *
 * @property integer $id
 * @property integer $dbid
 * @property string $name
 * @property string $type
 * @property boolean $default
 * @property boolean $protected
 * @property integer $permission_id
 * @property integer $qualification_id
 * @property-read \App\Models\Mship\Permission $permission
 * @property-read \App\Models\Mship\Qualification $qualification
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ChannelGroup whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ChannelGroup whereDbid($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ChannelGroup whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ChannelGroup whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ChannelGroup whereDefault($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ChannelGroup whereProtected($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ChannelGroup wherePermissionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\ChannelGroup whereQualificationId($value)
 * @mixin \Eloquent
 */
class ChannelGroup extends Group
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GroupScope);
    }
}
