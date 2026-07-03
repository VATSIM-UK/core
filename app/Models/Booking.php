<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Atc\Position;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Booking extends Model
{
    use HasFactory;

    const TYPE_STANDARD = 'standard';

    const TYPE_EXAM = 'exam';

    const TYPE_MENTORING = 'mentoring';

    const TYPE_EVENT = 'event';

    const TYPE_GROUP_SEMINAR = 'group_seminar';

    protected $fillable = [
        'position_id',
        'member_id',
        'type',
        'starts_at',
        'ends_at',
        'bookable_type',
        'bookable_id',
        'cts_booking_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'member_id');
    }

    public function bookable(): MorphTo
    {
        return $this->morphTo();
    }

    public function ctsBooking(): BelongsTo
    {
        return $this->belongsTo(Cts\Booking::class, 'cts_booking_id', 'id');
    }

    public function scopeOverlapping(Builder $query, Carbon $startsAt, Carbon $endsAt, int $positionId): Builder
    {
        return $query->where('position_id', $positionId)
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeLiveAtc(Builder $query): Builder
    {
        return $query->whereNotNull('position_id')
            ->whereHas('position', fn (Builder $q) => $q->whereIn('type', [
                Position::TYPE_DELIVERY,
                Position::TYPE_GROUND,
                Position::TYPE_TOWER,
                Position::TYPE_APPROACH,
                Position::TYPE_ENROUTE,
                Position::TYPE_FSS,
            ]));
    }

    public function scopeNotEvent(Builder $query): Builder
    {
        return $query->where('type', '!=', self::TYPE_EVENT);
    }
}
