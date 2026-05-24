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
     * Mentoring panel pages are gated behind the training beta flag.
     * Temporary until mentoring pages are released publically.
     */
    public function before(Account $user, string $ability): ?bool
    {
        if (! app()->runningUnitTests() && ! $user->can('training.beta')) {
            return false;
        }

        return null;
    }

    // View permission

    /**
     * Access mentoring pages.
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
     * View positions/callsigns for a category that a user has permissions to conduct mentoring sessions,
     * including all positions when the user may view all.
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
        // Students should always be able to view their own reports
        if ($session->student_id === $user->id) {
            return true;
        }

        // Mentors should always be able to view their own reports
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

    public function viewUpcomingSessions(Account $user): bool
    {
        if ($this->viewAll($user)) {
            return true;
        }

        return $user->can('training.mentors.view.atc') || $user->can('training.mentors.view.pilot');
    }

    // Action permissions

    /**
     * Check if a user has permission to mentor a specific position.
     */
    public function mentorPosition(Account $user, string $position): bool
    {
        if ($this->viewAll($user)) {
            return true;
        }

        return in_array($position, $user->getAllAssignedCallsigns(), true);
    }
}
