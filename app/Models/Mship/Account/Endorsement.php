<?php

namespace App\Models\Mship\Account;

use App\Models\Atc\PositionGroup;
use App\Models\Model;
use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class Endorsement extends Model
{
    use SoftDeletes;

    protected $table = 'mship_account_endorsement';

    protected $guarded = [];

    protected $with = ['positionGroup'];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function positionGroup()
    {
        return $this->belongsTo(PositionGroup::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function type(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => is_null($attributes['expired_at']) ? 'Permanent' : 'Temporary',
        );
    }
}
