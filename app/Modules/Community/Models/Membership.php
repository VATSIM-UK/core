<?php

namespace App\Modules\Community\Models;

use App\Models\Model;

/**
 * App\Modules\Community\Models\Membership
 *
 * @property int $id
 * @property int $group_id
 * @property int $account_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Membership whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Membership whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Membership whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Membership whereGroupId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Membership whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Community\Models\Membership whereUpdatedAt($value)
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
