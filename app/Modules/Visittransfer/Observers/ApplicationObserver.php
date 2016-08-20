<?php namespace App\Modules\Visittransfer\Observers;

use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Events\ApplicationCreated;
use App\Modules\Visittransfer\Events\ApplicationUpdated;
use App\Modules\Visittransfer\Events\ApplicationAccepted;
use App\Modules\Visittransfer\Events\ApplicationRejected;

class ApplicationObserver {

    public function created($model){
        event(new ApplicationCreated($model));
    }

    public function updated($model){
        event(new ApplicationUpdated($model));

        if($model->status == Application::STATUS_REJECTED){
            event(new ApplicationRejected($model));
        }

        if($model->status == Application::STATUS_ACCEPTED){
            event(new ApplicationAccepted($model));
        }
    }
}