<?php

namespace App\Models\Training\TrainingPlace;

use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Observers\Training\TrainingPlaceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([TrainingPlaceObserver::class])]
class TrainingPlace extends Model
{
    /** @use HasFactory<\Database\Factories\Training\TrainingPlace\TrainingPlaceFactory> */
    use HasFactory;

    use HasUlids;

    protected $guarded = [];

    public function waitingListAccount(): BelongsTo
    {
        return $this->belongsTo(WaitingListAccount::class, 'waiting_list_account_id')
            ->withTrashed();
    }

    public function trainingPosition(): BelongsTo
    {
        return $this->belongsTo(TrainingPosition::class, 'training_position_id');
    }
}
