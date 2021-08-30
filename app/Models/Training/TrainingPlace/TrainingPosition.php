<?php

namespace App\Models\Training\TrainingPlace;

use App\Models\Station;
use App\Models\Training\TrainingPlace;
use App\Models\Training\WaitingList;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingPosition extends Model
{
    use HasFactory;

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function trainingPlaces(): HasMany
    {
        return $this->hasMany(TrainingPlace::class);
    }

    public function waitingList(): BelongsTo
    {
        return $this->belongsTo(WaitingList::class);
    }

    /**
     * Generate a list of available training positions for a waiting list
     * based upon the places configured and also related.
     *
     * @return array
     */
    public static function availablePlacesForWaitingList(WaitingList $waitingList)
    {
        $positions = self::with('station')
            ->withCount('trainingPlaces')
            ->where('waiting_list_id', $waitingList->id)
            ->get();

        return $positions->reject(function ($position) {
            return (int) $position->training_places_count === (int) $position->places;
        })->map(function ($position) {
            return ['id' => $position->id, 'callsign' => $position->station->callsign];
        })->toArray();
    }
}
