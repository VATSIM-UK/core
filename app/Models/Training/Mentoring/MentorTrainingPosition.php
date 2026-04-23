<?php

declare(strict_types=1);

namespace App\Models\Training\Mentoring;

use App\Models\Mship\Account;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorTrainingPosition extends Model
{
    protected $fillable = [
        'account_id',
        'training_position_id',
        'created_by',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function trainingPosition(): BelongsTo
    {
        return $this->belongsTo(TrainingPosition::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'created_by');
    }
}
