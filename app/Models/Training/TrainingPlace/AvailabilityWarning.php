<?php

namespace App\Models\Training\TrainingPlace;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
