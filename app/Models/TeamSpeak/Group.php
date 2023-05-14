<?php

namespace App\Models\TeamSpeak;

use App\Models\Model;
use App\Models\Mship\Qualification;
use Spatie\Permission\Models\Permission;

/**
 * App\Models\TeamSpeak\Group.
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Group whereDbid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Group whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Group whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Group wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Group whereProtected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Group whereQualificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Group whereType($value)
 *
 * @mixin \Eloquent
 */
class Group extends Model
{
    public $timestamps = false;

    protected $table = 'teamspeak_group';

    protected $primaryKey = 'id';

    /**
     * The permission a user should have in order to be in this group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * The qualification a user should have in order to be in this group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function qualification()
    {
        return $this->belongsTo(Qualification::class);
    }
}
