<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

trait RecordsActivity
{
    protected static function boot()
    {
        parent::boot();

        foreach(static::getModelEvents() as $event){
            static::$event(function (Model $model) use ($event) {
                $model->addActivity($event);
            });
        }
    }

    public static function getModelEvents(){
        if(isset(static::$recordEvents)){
            return static::$recordEvents;
        }

        return [
            "created", "updated", "deleted",
        ];
    }

    public function addActivity($event){
        Activity::create([
            'actor_id'     => 0,
            'subject_id'   => $this->getKey(),
            'subject_type' => get_class($this),
            'identifier'   => $this->getActivityIdentifier($event),
        ]);
    }

    protected function getActivityIdentifier($action){
        $name = strtolower((new ReflectionClass($this))->getShortName());
    }
}