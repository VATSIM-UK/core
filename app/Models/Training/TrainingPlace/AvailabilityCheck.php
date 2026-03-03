<?php

declare(strict_types=1);

namespace App\Models\Training\TrainingPlace;

use App\Enums\AvailabilityCheckStatus;
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

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status' => AvailabilityCheckStatus::class,
    ];

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public function trainingPlace(): BelongsTo
    {
        return $this->belongsTo(TrainingPlace::class, 'training_place_id');
    }
}
