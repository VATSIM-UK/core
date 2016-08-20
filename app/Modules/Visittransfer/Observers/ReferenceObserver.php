<?php namespace App\Modules\Visittransfer\Observers;

use App\Modules\Visittransfer\Events\ReferenceAccepted;
use App\Modules\Visittransfer\Events\ReferenceRejected;
use App\Modules\Visittransfer\Events\ReferenceUnderReview;
use App\Modules\Visittransfer\Models\Reference;

class ReferenceObserver {

    public function updated($model){
        if($model->status == Reference::STATUS_UNDER_REVIEW){
            event(new ReferenceUnderReview($model));
        }

        if($model->status == Reference::STATUS_ACCEPTED){
            event(new ReferenceUnderReview($model));
        }

        if($model->status == Reference::STATUS_REJECTED){
            event(new ReferenceUnderReview($model));
        }
    }
}