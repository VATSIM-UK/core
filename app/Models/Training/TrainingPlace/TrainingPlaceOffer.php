<?php

namespace App\Models\Training\TrainingPlace;

use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList\WaitingListAccount;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\TrainingPlaceOfferStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TrainingPlaceOffer extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'waiting_list_account_id',
        'training_position_id',
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

    public function trainingPosition(): BelongsTo
    {
        return $this->belongsTo(TrainingPosition::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public static function getExpiredOffers(Carbon $date): Collection
    {
        return TrainingPlaceOffer::where('status', TrainingPlaceOfferStatus::Pending)
            ->where('expires_at', '<', now())
            ->get();
    }
}
