<?php

namespace App\Models\Cts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Availability extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'availability';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'from' => 'datetime',
    ];

    protected $attributes = [
        'type' => 'S',
    ];

    // Temporary handling for `to` being set to "24:00" from legacy CTS system
    protected function to(): Attribute
    {
        return Attribute::make(get: function ($value) {
            if (! $value) {
                return null;
            }

            // A slot that runs to the end of the day is stored as 24:00 by the availability
            // grid and as 00:00 by rows written before it did, so both read as 23:45.
            if (str_starts_with($value, '24:') || ($value === '00:00:00' && $this->from?->format('H:i:s') !== '00:00:00')) {
                return Carbon::parse('23:45:00');
            }

            return Carbon::parse($value);
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'student_id', 'id');
    }

    /**
     * UTC start and end; the end rolls to the next day when `to` is before `from`.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function bounds(): array
    {
        $date = $this->date->format('Y-m-d');

        // A slot that runs to the end of the day is stored as 24:00, or as 00:00 by rows
        // written before the grid did that, so read the raw value to close it at midnight.
        $storedEnd = (string) ($this->getAttributes()['to'] ?? '');
        $endTime = in_array($storedEnd, ['24:00:00', '00:00:00'], true) ? '00:00:00' : $this->to->format('H:i:s');

        $start = Carbon::parse($date.' '.$this->from->format('H:i:s'), 'UTC');
        $end = Carbon::parse($date.' '.$endTime, 'UTC');

        if ($endTime < $this->from->format('H:i:s')) {
            $end->addDay();
        }

        return [$start, $end];
    }

    public function getFormattedTimeSlotAttribute(): string
    {
        return $this->date->format('Y-m-d').' from '.
               $this->from->format('H:i').' to '.
               $this->to->format('H:i');
    }
}
