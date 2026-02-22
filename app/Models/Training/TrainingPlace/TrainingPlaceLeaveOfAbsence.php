<?php

namespace App\Models\Training\TrainingPlace;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingPlaceLeaveOfAbsence extends Model
{
    protected $table = 'training_place_leave_of_absences';

    protected $fillable = [
        'training_place_id',
        'begins_at',
        'ends_at',
        'reason',
    ];

    protected $casts = [
        'begins_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function trainingPlace(): BelongsTo
    {
        return $this->belongsTo(TrainingPlace::class);
    }

    public function scopeCurrent(Builder $query)
    {
        return $query->where('begins_at', '<=', now())->where('ends_at', '>=', now());
    }

    public function scopeOverlapping(Builder $query, \DateTimeInterface $start, \DateTimeInterface $end)
    {
        return $query->where('begins_at', '<', $end)->where('ends_at', '>', $start);
    }

    public function isActive()
    {
        return now()->between($this->begins_at, $this->ends_at);
    }

    public static function roundEndsAtToEndOfDay(mixed $value)
    {
        return Carbon::parse($value)->endOfDay();
    }
}
