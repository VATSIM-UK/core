<?php

namespace App\Models\TeamSpeak;

use App\Models\aModel as Model;
use App\Models\Mship\Permission;
use App\Models\Mship\Qualification;

/**
 * App\Models\TeamSpeak\Group
 *
 * @property-read \App\Models\Mship\Permission $permission
 * @property-read \App\Models\Mship\Qualification $qualification
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
