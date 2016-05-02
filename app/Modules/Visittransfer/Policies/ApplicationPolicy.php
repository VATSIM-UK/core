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
        if(Gate::denies("update", $application)){
            return false;
        }

        return $application->facility_id == null;
    }

    public function addStatement(Account $user, Application $application){
        if(Gate::allows("select-facility", $application)){
            return false;
        }

        return $application->statement == null;
    }
}