<?php

declare(strict_types=1);

namespace App\Models\Training\TrainingPlace;

use App\Enums\AvailabilityLogEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityLogEntry extends Model
{
    /** @use HasFactory<\Database\Factories\Training\TrainingPlace\AvailabilityLogEntryFactory> */
    use HasFactory;

    protected $table = 'availability_log';

    public const UPDATED_AT = null;

    protected $fillable = [
        'training_place_id',
        'event',
        'slot_from',
        'slot_to',
        'created_at',
        'superseded_at',
    ];

    protected $casts = [
        'event' => AvailabilityLogEvent::class,
        'slot_from' => 'datetime',
        'slot_to' => 'datetime',
        'created_at' => 'datetime',
        'superseded_at' => 'datetime',
    ];

    public function trainingPlace(): BelongsTo
    {
        return $this->belongsTo(TrainingPlace::class);
    }
}
