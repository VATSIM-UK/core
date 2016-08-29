<?php namespace App\Modules\Visittransfer\Policies;

use App\Models\Mship\Account;
use App\Models\Mship\Account\State;
use App\Modules\Visittransfer\Models\Application;
use Auth;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class ApplicationPolicy {
    use HandlesAuthorization;

    public function before(Account $user){
        // If they are currently a division member, they are not authorised.
        if($user->hasState(State::STATE_DIVISION)){
            return false;
        }
    }

    public function create(Account $user, Application $application){
        // If they currently HAVE an application open, then they are not authorised.
        $countCurrentApplications = $user->visitTransferApplications()
                                         ->open()
                                         ->count();

        if($countCurrentApplications > 0){
            return false;
        }

        return true;
    }

    public function update(Account $user, Application $application){
        if($application->status == Application::STATUS_IN_PROGRESS){
            return true;
        }

        return false;
    }

    public function selectFacility(Account $user, Application $application){
        if(!$application->exists){
            return false;
        }

        return $application->facility_id == null;
    }

    public function addStatement(Account $user, Application $application){
        if(!$application->facility){
            return false;
        }

        if(!$application->statement_required){
            return false;
        }

        return true;
    }

    public function addReferee(Account $user, Application $application){
        if(!$application->facility){
            return false;
        }

        if($application->references_required === 0){
            return false;
        }

        if($application->statement == null && $application->statement_required){
            return false;
        }

        return true;
    }

    public function submitApplication(Account $user, Application $application){
        if(!$application->facility){
            return false;
        }

        if($application->statement == null && $application->statement_required){
            return false;
        }

        if($application->number_references_required_relative > 0){
            return false;
        }

        if(!$application->is_in_progress){
            return false;
        }

        return true;
    }

    public function viewApplication(Account $user, Application $application){
        return $application && $user->id == $application->account_id;
    }

    public function accept(Account $user, Application $application){
        // TODO: Figure this permission stuff out for ACP.
        return true;
    }

    public function reject(Account $user, Application $application){
        // TODO: Figure this permission stuff out for ACP.
        return true;
    }
}