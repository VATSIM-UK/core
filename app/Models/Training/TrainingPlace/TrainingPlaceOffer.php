<?php

namespace App\Models\Training\TrainingPlace;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Training\TrainingPlace\TrainingPosition;

class TrainingPlaceOffer extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'offer_id';

    protected $fillable = [
        'account_id',
        'offer_id',
        'offered_by',
        'expires_at',
        'reminder_sent_at',
        'training_position_id',
    ];

    public function account() : BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function trainingPosition() : BelongsTo
    {
        return $this->belongsTo(TrainingPosition::class);
    }
}
