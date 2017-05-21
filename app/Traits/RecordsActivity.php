<?php

namespace App\Traits;

use Auth;
use App\Models\Sys\Activity;
use Illuminate\Database\Eloquent\Model;

trait RecordsActivity
{
    public static function boot()
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
        Activity::create([
            'actor_id' => (Auth::check() ? Auth::id() : 0),
            'subject_id' => $this->getKey(),
            'subject_type' => get_class($this),
            'action' => $event,
        ]);
    }

    abstract public function __toString();
}
