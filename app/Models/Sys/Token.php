<?php

namespace App\Models\Sys;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Sys\Token
 *
 * @property int $token_id
 * @property int $related_id
 * @property string $related_type
 * @property string $type
 * @property string $code
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $expires_at
 * @property \Carbon\Carbon $used_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read mixed $display_value
 * @property-read mixed $is_expired
 * @property-read mixed $is_used
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $related
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token expired()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token hasCode($code)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token notExpired()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token notUsed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token ofType($type)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token used()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token valid()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereExpiresAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereRelatedId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereRelatedType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereTokenId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Token whereUsedAt($value)
 * @mixin \Eloquent
 */
class Token extends \App\Models\Model
{
    use SoftDeletingTrait;

    protected $table = 'sys_token';
    protected $primaryKey = 'token_id';
    protected $dates = ['created_at', 'updated_at', 'expires_at', 'used_at', 'deleted_at'];
    protected $hidden = ['token_id'];

    public function getRouteKeyName()
    {
        return 'code';
    }

    public function related()
    {
        return $this->morphTo();
    }

    public function scopeHasCode($query, $code)
    {
        return $query->where('code', '=', $code);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', '=', $type);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', \Carbon\Carbon::now()->toDateTimeString());
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>=', \Carbon\Carbon::now()->toDateTimeString());
    }

    public function scopeUsed($query)
    {
        return $query->whereNotNull('used_at');
    }

    public function scopeNotUsed($query)
    {
        return $query->whereNull('used_at');
    }

    public function scopeValid($query)
    {
        return $query->notUsed()->notExpired();
    }

    public static function generate($type, $allowDuplicates = false, $relation = null, $expireMinutes = 1440)
    {
        if ($allowDuplicates == false) {
            foreach ($relation->tokens()->whereType($type)->notExpired()->get() as $t) {
                $t->delete();
            }
        }

        $token = new self;
        $token->type = $type;
        $token->expires_at = \Carbon\Carbon::now()->addMinutes($expireMinutes)->toDateTimeString();
        $token->code = uniqid(uniqid());

        if ($relation != null) {
            $relation->tokens()->save($token);
        } else {
            $token->save();
        }

        return $token;
    }

    public function consume()
    {
        if (!$this || $this->is_used || $this->is_expired) {
            return false;
        }

        $this->used_at = \Carbon\Carbon::now();
        $this->save();
    }

    public function getIsUsedAttribute()
    {
        return $this->used_at != null && $this->used_at->isPast();
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at != null && $this->expires_at->isPast();
    }

    public function __toString()
    {
        return array_get($this->attributes, 'code', 'NoValue');
    }

    public function getDisplayValueAttribute()
    {
    }
}
