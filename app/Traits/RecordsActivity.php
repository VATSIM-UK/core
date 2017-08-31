<?php

namespace App\Traits;

use App\Models\Sys\Activity;
use Auth;
use Illuminate\Database\Eloquent\Model;

trait RecordsActivity
{
    protected static function boot()
    {
        parent::boot();

        foreach (static::getModelEvents() as $event) {
            static::$event(function (Model $model) use ($event) {
                $model->addActivity($event);
            });
        }
    }

    public static function getModelEvents()
    {
        if (isset(static::$recordEvents)) {
            return static::$recordEvents;
        }

        return [
            'created',
            'updated',
            'deleted',
        ];
    }

    public function addActivity($event)
    {
        if (Auth::check()) {
            Activity::create([
                'actor_id' => Auth::id(),
                'subject_id' => $this->getKey(),
                'subject_type' => get_class($this),
                'action' => $event,
            ]);
        }
    }
}
