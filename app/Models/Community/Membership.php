<?php

namespace App\Models\Community;

use App\Models\Model;

/**
 * App\Models\Community\Membership
 *
 * @property int $id
 * @property int $group_id
 * @property int $account_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Membership whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Membership whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Membership whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Membership whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Membership whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Community\Membership whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Membership extends Model
{
    protected $table = 'community_membership';
    protected $primaryKey = 'id';
    public $timestamps = true;
    public $dates = ['created_at', 'updated_at', 'deleted_at'];
    public $fillable = [
        'group_id',
        'account_id',
    ];
}
