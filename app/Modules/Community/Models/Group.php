<?php

namespace App\Modules\Community\Models;

use App\Models\Model;
use App\Models\Mship\Account;

/**
 * App\Modules\Community\Models\Group
 *
 * @property int $id
 * @property string $name
 * @property int $tier
 * @property string $coordinate_boundaries
 * @property bool $default
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account[] $accounts
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Group inTier($tier)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Group isDefault()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Group notDefault()
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Group whereCoordinateBoundaries($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Group whereDefault($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Group whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Group whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Group whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Group whereTier($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Group whereUpdatedAt($value)
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
