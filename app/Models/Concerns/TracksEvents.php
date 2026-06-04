<?php

namespace App\Models\Concerns;

use App;
use App\Models\Sys\Activity;
use Auth;

trait TracksEvents
{
    /**
     * Model events that will be tracked.
     *
     * @var array
     */
    protected $trackedEvents = [];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function bootTracksEvents()
    {
        $instance = new static;

        foreach ($instance->trackedEvents as $event) {
            static::$event(function ($model) use ($event) {
                $model->addActivity($event);
            });
        }
    }

    /**
     * Create an activity for the event.
     */
    public function addActivity($event)
    {
        if (! App::runningInConsole()) {
            Activity::create([
                'actor_id' => Auth::check() ? Auth::id() : null,
                'subject_id' => $this->getKey(),
                'subject_type' => get_class($this),
                'action' => $event,
            ]);
        }
    }
}
