<?php

namespace App\Models\Training\TrainingPlace;

use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList\WaitingListAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingPlace extends Model
{
    /** @use HasFactory<\Database\Factories\Training\TrainingPlace\TrainingPlaceFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

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
