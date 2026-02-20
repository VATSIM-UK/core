<?php

namespace App\Models\Training\TrainingPosition;

use App\Models\Atc\Position;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\WaitingList;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingPosition extends Model
{
    /** @use HasFactory<\Database\Factories\Training\TrainingPosition\TrainingPositionFactory> */
    use HasFactory;

    protected $casts = [
        'cts_positions' => 'array',
    ];

    protected $guarded = [];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function trainingPlaces(): HasMany
    {
        return $this->hasMany(TrainingPlace::class, 'training_position_id');
    }

    public function waitingLists(): BelongsToMany
    {
        return $this->belongsToMany(
            WaitingList::class,
            'training_position_waiting_list',
            'training_position_id',
            'waiting_list_id'
        )->withTimestamps();
    }
}
