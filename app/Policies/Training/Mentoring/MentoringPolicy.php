<?php

declare(strict_types=1);

namespace App\Policies\Training\Mentoring;

use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\Mentoring\MentoringScope;
use App\Services\Training\MentorPermissionService;
use Illuminate\Auth\Access\HandlesAuthorization;

class MentoringPolicy
{
    use HandlesAuthorization;

    /**
     * Mentoring panel pages are gated behind the training beta flag outside of tests.
     */
    public function before(Account $user, string $ability): ?bool
    {
        if (! app()->runningUnitTests() && ! $user->can('training.beta')) {
            return false;
        }

        return null;
    }

    /**
     * Access mentoring session pages.
     */
    public function viewAny(Account $user): bool
    {
        if ($this->viewAll($user)) {
            return true;
        }

        return $user->mentorTrainingPositions()->exists();
    }

    /**
     * View all mentoring data across training groups (admin bypass).
     */
    public function viewAll(Account $user): bool
    {
        return $user->can('training.mentoring.view.*');
    }

    /**
     * View mentoring data for a specific training group category.
     */
    public function viewCategory(Account $user, MentoringScope $scope, string $category): bool
    {
        if ($this->viewAll($user)) {
            return true;
        }

        return ! empty($user->getAssignedCallsignsForCategory($category));
    }

    /**
     * View positions/callsigns for a category, including all positions when the user may view all.
     */
    public function visibleCtsPositionsForCategory(Account $user, MentoringScope $scope, string $category): array
    {
        if ($this->viewAll($user)) {
            return app(MentorPermissionService::class)->getAllCtsCallsignsForCategory($category);
        }

        return $user->getAssignedCallsignsForCategory($category);
    }

    /**
     * View a mentoring session report.
     */
    public function view(Account $user, Session $session): bool
    {
        if ($session->student_id === $user->id) {
            return true;
        }

        if ($session->mentor_id === $user->id) {
            return true;
        }

        if ($this->viewAll($user)) {
            return true;
        }

        return $user->canMentorPosition($session->position);
    }

    /**
     * Link from a mentoring report to the student's training place page.
     */
    public function viewStudentTrainingPlace(Account $user): bool
    {
        return $user->can('training-places.view.*');
    }
}
