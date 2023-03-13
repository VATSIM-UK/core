<?php

namespace App\Models\TeamSpeak;

use App\Scopes\TeamSpeak\GroupScope;

/**
 * App\Models\TeamSpeak\ChannelGroup.
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\ChannelGroup whereDbid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\ChannelGroup whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\ChannelGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\ChannelGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\ChannelGroup wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\ChannelGroup whereProtected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\ChannelGroup whereQualificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\ChannelGroup whereType($value)
 *
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
