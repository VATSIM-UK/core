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
        return $application->facility_id == null;
    }

    public function addStatement(Account $user, Application $application){
        if(!$application->facility){
            return false;
        }

        return $application->statement == null;
    }

    public function addReferee(Account $user, Application $application){
        if($application->statement == null){
            return false;
        }

        if($application->number_references_required_relative == 0){
            return false;
        }

        return true;
    }

    public function submitApplication(Account $user, Application $application){
        if(!$application->facility){
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
}