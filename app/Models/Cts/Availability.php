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
        return Attribute::make(get: fn ($value) => $value ? Carbon::parse(str_replace('24:00', '23:45', $value)) : null);
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

        $start = Carbon::parse($date.' '.$this->from->format('H:i:s'), 'UTC');
        $end = Carbon::parse($date.' '.$this->to->format('H:i:s'), 'UTC');

        if ($this->to->format('H:i:s') < $this->from->format('H:i:s')) {
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
