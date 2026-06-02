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
