<?php

namespace App\Observers;

use App;
use App\Models\Sys\Activity;
use Auth;

class ModelActivityObserver
{
    public function created($model)
    {
        $this->addActivity($model);
    }

    public function updated($model)
    {
        $this->addActivity($model);
    }

    public function deleted($model)
    {
        $this->addActivity($model);
    }

    public function addActivity($model)
    {
        $event = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        if (!App::runningInConsole()) {
            Activity::create([
                'actor_id' => Auth::check() ? Auth::id() : null,
                'subject_id' => $model->getKey(),
                'subject_type' => get_class($model),
                'action' => $event,
            ]);
        }
    }
}
