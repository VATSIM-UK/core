<?php

namespace App\Models\Training\TrainingPlace;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityCheck extends Model
{
    /** @use HasFactory<\Database\Factories\Training\TrainingPlace\AvailabilityCheckFactory> */
    use HasFactory;

    use HasUlids;

    protected $guarded = [];

    public function trainingPlace(): BelongsTo
    {
        return $this->belongsTo(TrainingPlace::class, 'training_place_id');
    }
}
