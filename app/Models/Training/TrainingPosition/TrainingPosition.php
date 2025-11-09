<?php

namespace App\Models\Training\TrainingPosition;

use App\Models\Atc\Position;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingPosition extends Model
{
    /** @use HasFactory<\Database\Factories\Training\TrainingPosition\TrainingPositionFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'cts_positions' => 'array',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function trainingPlaces(): HasMany
    {
        return $this->hasMany(TrainingPlace::class, 'training_position_id');
    }
}
