<?php

namespace App\Models\Mship\Concerns;

use App\Models\Cts\Session;
use App\Models\Training\Mentoring\MentoringScope;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Services\Training\MentorPermissionService;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasMentoringPermissions
{
    public function mentorTrainingPositions(): HasMany
    {
        return $this->hasMany(MentorTrainingPosition::class, 'account_id');
    }

    public function getAssignedCallsignsForCategory(string $category): array
    {
        return app(MentorPermissionService::class)->getAssignedCtsCallsigns($this, $category);
    }

    public function getAllAssignedCallsigns(): array
    {
        return collect($this->getAvailableMentoringCategories())
            ->flatMap(fn (string $cat) => $this->getAssignedCallsignsForCategory($cat))
            ->unique()
            ->values()
            ->toArray();
    }

    public function getAvailableMentoringCategories(): array
    {
        $allCategories = array_merge(
            MentorPermissionService::atcCategories(),
            MentorPermissionService::pilotCategories()
        );

        if ($this->can('viewAll', Session::class)) {
            return $allCategories;
        }

        return collect($allCategories)
            ->filter(fn (string $cat) => $this->hasMentoringPermissionForCategory($cat))
            ->values()
            ->toArray();
    }

    public function hasMentoringPermissionForCategory(string $category): bool
    {
        return $this->can('viewCategory', [new MentoringScope, $category]);
    }

    public function hasMentoringPermissionForPosition(string $position): bool
    {
        if ($this->can('viewAll', Session::class)) {
            return true;
        }

        return in_array($position, $this->getAllAssignedCallsigns(), true);
    }

    public function canMentorPosition(string $position): bool
    {
        return $this->mentorTrainingPositions()->get()->contains(fn ($mtp) => in_array($position, app(MentorPermissionService::class)->getCtsCallsignsForMentorable($mtp->mentorable)));
    }
}
