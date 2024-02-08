<?php

namespace App\Models\Mship\Account;

use App\Models\Model;
use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Endorsement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mship_account_endorsement';

    protected $guarded = [];

    protected $with = ['endorsable'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function endorsable()
    {
        return $this->morphTo();
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function type(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => is_null($attributes['expires_at']) ? 'Permanent' : 'Temporary',
        );
    }

    public function expires(): bool
    {
        return isset($this->expires_at);
    }

    public function hasExpired(): bool
    {
        return ! is_null($this->expires_at) && $this->expires_at->isPast();
    }

    public function scopeActive(Builder $query)
    {
        return $query->whereNull('expires_at')
            ->orWhereDate('expires_at', '>=', now());
    }
}
