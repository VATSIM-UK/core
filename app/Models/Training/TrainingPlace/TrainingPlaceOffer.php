<?php

namespace App\Models\Training\TrainingPlace;

use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Mship\Qualification;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList\WaitingListAccount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class TrainingPlaceOffer extends Model
{
    /** @use HasFactory<\Database\Factories\Training\TrainingPlace\TrainingPlaceOfferFactory> */
    use HasFactory;

    protected $fillable = [
        'waiting_list_account_id',
        'trainable_type',
        'trainable_id',
        'token',
        'expires_at',
        'response_at',
        'status',
        'decline_reason',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'response_at' => 'datetime',
        'status' => TrainingPlaceOfferStatus::class,
    ];

    public function waitingListAccount(): BelongsTo
    {
        return $this->belongsTo(WaitingListAccount::class);
    }

    public function trainable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function trainingPosition(): Attribute
    {
        return Attribute::make(
            get: fn (): ?TrainingPosition => $this->trainable instanceof TrainingPosition ? $this->trainable : null,
        );
    }

    protected function qualification(): Attribute
    {
        return Attribute::make(
            get: fn (): ?Qualification => $this->trainable instanceof Qualification ? $this->trainable : null,
        );
    }

    protected function displayName(): Attribute
    {
        return Attribute::make(get: function (): string {
            $trainable = $this->trainable;

            if ($trainable instanceof TrainingPosition) {
                return $trainable->position?->callsign
                    ?? collect($trainable->cts_positions)->filter()->first()
                    ?? "Position {$trainable->id}";
            }

            if ($trainable instanceof Qualification) {
                return $trainable->name_long ?? $trainable->name_small ?? $trainable->code ?? "Qualification {$trainable->id}";
            }

            return 'Unknown';
        });
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(get: fn () => $this->status->label());
    }

    protected function isExpired(): Attribute
    {
        return Attribute::make(get: fn () => $this->status === TrainingPlaceOfferStatus::Expired || $this->expires_at->isPast());
    }

    public static function getExpiredOffers(Carbon $date): Collection
    {
        return TrainingPlaceOffer::where('status', TrainingPlaceOfferStatus::Pending)
            ->where('expires_at', '<', now())
            ->get();
    }
}
