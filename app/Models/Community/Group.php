<?php

namespace App\Models\Community;

use App\Models\Model;
use App\Models\Mship\Account;

/**
 * App\Models\Community\Group
 *
 * @property int $id
 * @property string $name
 * @property int|null $tier
 * @property string|null $coordinate_boundaries
 * @property int $default
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account[] $accounts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Group inTier($tier)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Group isDefault()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Group notDefault()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Group whereCoordinateBoundaries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Group whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Group whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Group whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Group whereTier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Group whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Group extends Model
{
    protected $table = 'community_group';
    protected $primaryKey = 'id';
    public $timestamps = true;
    public $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $fillable = [
        'name',
        'coordinate_boundaries',
    ];

    public function accounts()
    {
        return $this->belongsToMany(\App\Models\Mship\Account::class, 'community_membership', 'group_id', 'account_id')
                    ->withTimestamps()
                    ->wherePivot('deleted_at', null);
    }

    public static function scopeIsDefault($query)
    {
        return $query->whereDefault(true);
    }

    public static function scopeNotDefault($query)
    {
        return $query->whereDefault(false);
    }

    public static function scopeInTier($query, $tier)
    {
        return $query->where('tier', '=', $tier);
    }

    public function hasMember(Account $member)
    {
        return $this->exists && $this->accounts->contains($member->id);
    }
}
