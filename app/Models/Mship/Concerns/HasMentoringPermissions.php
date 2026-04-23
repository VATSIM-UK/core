<?php

namespace App\Models\Mship\Concerns;

use App\Models\Training\Mentoring\MentorTrainingPosition;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasMentoringPermissions
{
    public function mentorTrainingPositions(): HasMany
    {
        return $this->hasMany(MentorTrainingPosition::class);
    }
}
