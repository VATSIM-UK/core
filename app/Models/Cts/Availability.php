<?php

namespace App\Models\Cts;

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
        'to' => 'datetime',
    ];

    protected $attributes = [
        'type' => 'S',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'student_id', 'id');
    }

    public function getFormattedTimeSlotAttribute(): string
    {
        return $this->date->format('Y-m-d').' from '.
               $this->from->format('H:i').' to '.
               $this->to->format('H:i');
    }
}
