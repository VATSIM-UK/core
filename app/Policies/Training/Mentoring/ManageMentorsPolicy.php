<?php

declare(strict_types=1);

namespace App\Policies\Training\Mentoring;

use App\Models\Mship\Account;
use App\Models\Training\Mentoring\ManageMentorsScope;
use App\Services\Training\MentorPermissionService;
use Illuminate\Auth\Access\HandlesAuthorization;

class ManageMentorsPolicy
{
    use HandlesAuthorization;

    /**
     * Access the manage mentors page.
     */
    public function viewAny(Account $user): bool
    {
        return $user->can('training.mentors.view.atc') || $user->can('training.mentors.view.pilot');
    }

    /**
     * View mentors within a training group category.
     */
    public function viewCategory(Account $user, ManageMentorsScope $scope, string $category): bool
    {
        return $user->can('training.mentors.view.'.MentorPermissionService::categoryType($category));
    }

    /**
     * Add, update, or remove mentors within a training group category.
     */
    public function manageCategory(Account $user, ManageMentorsScope $scope, string $category): bool
    {
        return $user->can('training.mentors.manage.'.MentorPermissionService::categoryType($category));
    }
}
