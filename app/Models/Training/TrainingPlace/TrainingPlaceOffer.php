<?php

namespace App\Models\Training\TrainingPlace;

use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList\WaitingListAccount;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingPlaceOffer extends Model
{
    use HasUlids;

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
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
}
