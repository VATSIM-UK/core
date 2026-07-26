<?php

namespace App\Models\Training\TrainingPosition;

use App\Models\Atc\Position;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\WaitingList;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Route;

class TrainingPosition extends Model
{
    /** @use HasFactory<\Database\Factories\Training\TrainingPosition\TrainingPositionFactory> */
    use HasFactory;

    protected $casts = [
        'cts_positions' => 'array',
        'feature_toggles' => 'array',
    ];

    protected $guarded = [];

    protected static function booted(): void
    {
        // The trainable_waiting_list pivot is polymorphic and cannot rely on a
        // database foreign key cascade, so detach linked waiting lists on delete.
        static::deleting(function (TrainingPosition $trainingPosition): void {
            $trainingPosition->waitingLists()->detach();
        });
    }

    protected const SYLLABUS_ROUTES = [
        'OBS to S1 Training' => 'site.policy.training.s1-syllabus',
        'S1 Training' => 'site.policy.training.s1-syllabus',
        'S2 Training' => 'site.policy.training.s2-syllabus',
        'S3 Training' => 'site.policy.training.s3-syllabus',
        'C1 Training' => 'site.policy.training.c1-syllabus',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function trainingPlaces(): MorphMany
    {
        return $this->morphMany(TrainingPlace::class, 'trainable');
    }

    public function trainingPlaceOffers(): MorphMany
    {
        return $this->morphMany(TrainingPlaceOffer::class, 'trainable');
    }

    public function waitingLists(): MorphToMany
    {
        return $this->morphToMany(
            WaitingList::class,
            'trainable',
            'trainable_waiting_list',
            'trainable_id',
            'waiting_list_id'
        )->withTimestamps();
    }

    public function getShouldShowRecentControllingAttribute(): bool
    {
        return $this->feature_toggles['show_recent_controlling'] ?? true;
    }

    public function getShouldShowSoloEndorsementAttribute(): bool
    {
        return $this->feature_toggles['show_solo_endorsement'] ?? true;
    }

    public function getSyllabusUrlAttribute(): ?string
    {
        $routeName = self::SYLLABUS_ROUTES[$this->category] ?? null;

        if (! $routeName) {
            return null;
        }

        return Route::has($routeName) ? route($routeName) : null;
    }
}
