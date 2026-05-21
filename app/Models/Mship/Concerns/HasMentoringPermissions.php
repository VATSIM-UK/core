<?php

namespace App\Models\Mship\Concerns;

use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Services\Training\MentorPermissionService;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasMentoringPermissions
{
    public function mentorTrainingPositions(): HasMany
    {
        return $this->hasMany(MentorTrainingPosition::class);
    }

    public function canMentorPosition(string $position): bool
    {
        return $this->mentorTrainingPositions()->get()->contains(fn ($mtp) => in_array($position, app(MentorPermissionService::class)->getCtsCallsignsForMentorable($mtp->mentorable)));
    }
}
