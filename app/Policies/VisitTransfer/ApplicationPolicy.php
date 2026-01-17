<?php

namespace App\Policies\VisitTransfer;

use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationPolicy
{
    use HandlesAuthorization;

    public function before(Account $user, $ability)
    {
        $application = \Request::route('applicationByPublicId');

        if ($application && $user->id != $application->account_id) {
            return false;
        }
    }

    public function viewAny(Account $user)
    {
        return $user->can('vt.application.view.*');
    }

    public function view(Account $user, Application $application)
    {
        return $user->can("vt.application.view.{$application->id}") || $user->id === $application->account_id;
    }

    public function accept(Account $user, Application $application)
    {
        return $user->can('vt.application.accept.*')
        && $application->check_outcome_90_day
        && $application->check_outcome_50_hours
        && $application->can_accept;
    }

    public function reject(Account $user, Application $application)
    {
        return $user->can('vt.application.reject.*') && $application->can_reject;
    }

    public function complete(Account $user, Application $application)
    {
        return $user->can('vt.application.complete.*') && $application->is_accepted;
    }

    public function cancel(Account $user, Application $application)
    {
        return $user->can('vt.application.cancel.*') && $application->is_accepted;
    }

    public function overrideChecks(Account $user, Application $application)
    {
        return $user->can('vt.application.accept.*')
        && $application->can_accept
        && (! $application->check_outcome_90_day || ! $application->check_outcome_50_hours);
    }

    public function create(Account $user, Application $application)
    {
        // If they are currently a division member, they are not authorised.
        if ($user->hasState('DIVISION')) {
            return false;
        }

        // If they currently HAVE an application open, then they are not authorised.
        $countCurrentApplications = $user->visitTransferApplications()
            ->open()
            ->count();

        if ($countCurrentApplications > 0) {
            return false;
        }

        return true;
    }

    public function update(Account $user, Application $application)
    {
        if ($application->status == Application::STATUS_IN_PROGRESS) {
            return true;
        }

        return false;
    }

    public function selectFacility(Account $user, Application $application)
    {
        if (! $application->exists || ! $application->is_editable) {
            return false;
        }

        return $application->facility_id == null;
    }

    public function addStatement(Account $user, Application $application)
    {
        if (! $application->facility || ! $application->is_editable) {
            return false;
        }

        if (! $application->statement_required) {
            return false;
        }

        return true;
    }

    public function addReferee(Account $user, Application $application)
    {
        if (! $application->facility || ! $application->is_editable) {
            return false;
        }

        if ($application->references_required === 0) {
            return false;
        }

        if ($application->statement == null && $application->statement_required) {
            return false;
        }

        return true;
    }

    public function deleteReferee(Account $user, Application $application)
    {
        $reference = \Request::route('reference');

        if (! $application->facility || ! $application->is_editable) {
            return false;
        }

        if ($reference->application->account->id != $user->id) {
            return false;
        }

        return true;
    }

    public function submitApplication(Account $user, Application $application)
    {
        if (! $application->facility || ! $application->is_editable) {
            return false;
        }

        if ($application->statement == null && $application->statement_required) {
            return false;
        }

        if ($application->number_references_required_relative > 0) {
            return false;
        }

        if (! $application->is_in_progress) {
            return false;
        }

        return true;
    }

    public function withdrawApplication(Account $user, Application $application)
    {
        if (! $application->is_withdrawable) {
            return false;
        }

        return true;
    }

    public function checkOutcome(Account $user, Application $application)
    {
        return $application->is_open;
    }

    public function settingToggle(Account $user, Application $application)
    {
        return $application->is_editable;
    }
}
