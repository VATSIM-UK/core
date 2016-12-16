<?php

namespace App\Modules\Community\Models;

use App\Models\Model;

class Group extends Model
{
    protected $table      = 'community_group';
    protected $primaryKey = 'id';
    public $timestamps    = true;
    public $dates         = ['created_at', 'updated_at', 'deleted_at'];
    public $fillable      = [
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

    public static function scopeInTier($query, $tier){
        return $query->where("tier", "=", $tier);
    }
}
