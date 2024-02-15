<?php

namespace App\Models\Mship\Account;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EndorsementRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    const STATUS_APPROVED = 'approved';

    const STATUS_REJECTED = 'rejected';

    public function endorsable()
    {
        return $this->morphTo();
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                $hasBeenActioned = (bool) $this->getAttribute('actioned_at');

                if (! $hasBeenActioned) {
                    return 'Pending';
                }

                $actionedType = $this->getAttribute('actioned_type');

                return match ($actionedType) {
                    self::STATUS_APPROVED => 'Approved',
                    self::STATUS_REJECTED => 'Rejected',
                };
            },
        );
    }

    public function typeForHumans(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->getAttribute('endorsable_type')) {
                    'App\Models\Atc\PositionGroup' => 'Tier 1 Endorsement',
                    'App\Models\Atc\Position' => 'Solo Endorsement',
                    'App\Models\Mship\Qualification' => 'Rating Endorsement',
                };
            },
        );
    }

    public function type(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->getAttribute('endorsable_type')) {
                    'App\Models\Atc\PositionGroup' => 'permanent',
                    'App\Models\Atc\Position' => 'temporary',
                    'App\Models\Mship\Qualification' => 'permanent',
                };
            },
        );
    }

    public function markApproved()
    {
        $this->update([
            'actioned_at' => now(),
            'actioned_type' => self::STATUS_APPROVED,
        ]);
    }
}
