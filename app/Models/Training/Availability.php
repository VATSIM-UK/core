<?php

namespace App\Models\Training;

use App\Models\Model;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Availability extends Model
{
    use HasFactory;

    protected $table = 'training_availability';

    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function startsAt(): Carbon
    {
        return $this->starts_at->copy();
    }

    public function endsAt(): Carbon
    {
        return $this->ends_at->copy();
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function bounds(): array
    {
        return [$this->startsAt(), $this->endsAt()];
    }

    public function crossesMidnight(): bool
    {
        $end = $this->ends_at;

        return ! $this->starts_at->isSameDay($end)
            && ! $end->equalTo($end->copy()->startOfDay());
    }

    public function scopeForAccount(Builder $query, int $accountId): Builder
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeStartingBetween(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('starts_at', [
            $from->copy()->startOfDay(),
            $to->copy()->endOfDay(),
        ]);
    }

    public function scopeStartingFrom(Builder $query, Carbon $day): Builder
    {
        return $query->where('starts_at', '>=', $day->copy()->startOfDay());
    }

    public function scopeEndingAfter(Builder $query, Carbon $instant): Builder
    {
        return $query->where('ends_at', '>', $instant);
    }

    public function scopeOnDate(Builder $query, Carbon $day): Builder
    {
        $start = $day->copy()->startOfDay();

        return $query->where('starts_at', '>=', $start)
            ->where('starts_at', '<', $start->copy()->addDay());
    }

    public function scopeOrderedByStart(Builder $query): Builder
    {
        return $query->orderBy('starts_at');
    }

    public static function storeSlot(int $accountId, Carbon $start, Carbon $end): self
    {
        return static::create([
            'account_id' => $accountId,
            'starts_at' => $start->copy()->utc(),
            'ends_at' => $end->copy()->utc(),
        ]);
    }

    public function updateSlot(Carbon $start, Carbon $end): bool
    {
        return $this->update([
            'starts_at' => $start->copy()->utc(),
            'ends_at' => $end->copy()->utc(),
        ]);
    }
}
