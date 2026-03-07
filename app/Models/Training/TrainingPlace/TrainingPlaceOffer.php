<?php

namespace App\Models\Training\TrainingPlace;

use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList\WaitingListAccount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class TrainingPlaceOffer extends Model
{
    /** @use HasFactory<\Database\Factories\Training\TrainingPlace\TrainingPlaceOfferFactory> */
    use HasFactory;

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
