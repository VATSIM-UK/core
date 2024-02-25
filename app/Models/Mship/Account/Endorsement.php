<?php

namespace App\Models\Mship\Account;

use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Model;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

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
            get: function (mixed $value, array $attributes) {
                return match ($attributes['endorsable_type']) {
                    PositionGroup::class => 'Tier 1 Endorsement',
                    Position::class => 'Solo Endorsement',
                    Qualification::class => 'Rating Endorsement',
                    default => 'Unknown'
                };
            },
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
