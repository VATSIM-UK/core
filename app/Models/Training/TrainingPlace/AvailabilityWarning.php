<?php

namespace App\Models\Training\TrainingPlace;

use App\Observers\AvailabilityWarningObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([AvailabilityWarningObserver::class])]
class AvailabilityWarning extends Model
{
    /** @use HasFactory<\Database\Factories\Training\TrainingPlace\AvailabilityWarningFactory> */
    use HasFactory;

    use HasUlids;

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'resolved_at' => 'datetime',
        'removal_actioned_at' => 'datetime',
    ];

    public function trainingPlace(): BelongsTo
    {
        return $this->belongsTo(TrainingPlace::class);
    }

    public function availabilityCheck(): BelongsTo
    {
        return $this->belongsTo(AvailabilityCheck::class);
    }

    public function resolvedAvailabilityCheck(): BelongsTo
    {
        return $this->belongsTo(AvailabilityCheck::class, 'resolved_availability_check_id');
    }
}
