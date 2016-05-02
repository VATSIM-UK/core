<?php namespace App\Modules\Visittransfer\Observers;

use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Events\ApplicationCreated;
use App\Modules\Visittransfer\Events\ApplicationUpdated;
use App\Modules\Visittransfer\Events\ApplicationAccepted;
use App\Modules\Visittransfer\Events\ApplicationRejected;

class ApplicationObserver {

    public function created($model){
        event(ApplicationCreated::class, [$model]);
    }

    public function updated($model){
        event(ApplicationUpdated::class, [$model]);

        if($model->status == Application::STATUS_REJECTED){
            event(ApplicationRejected::class, [$model]);
        }

        if($model->status == Application::STATUS_ACCEPTED){
            event(ApplicationAccepted::class, [$model]);
        }
    }
}