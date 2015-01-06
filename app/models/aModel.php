<?php

namespace Models;

use \Models\Sys\Timeline\Entry;

abstract class aModel extends \Eloquent {
    public static function boot() {
        parent::boot();
        self::created(array(get_called_class(), "eventCreated"));
        self::updated(array(get_called_class(), "eventUpdated"));
        self::deleted(array(get_called_class(), "eventDeleted"));
    }

    public static function eventCreated($model) {
        return;
    }

    public static function eventUpdated($model) {
        return;
    }

    public static function eventDeleted($model) {
        return;
    }

    public function toArray() {
        $array = parent::toArray();
        $array['status'] = ($this->deleted_at ? "Deleted" : "Active");
        if (isset($array['pivot'])) {
            unset($array['pivot']);
        }
        return $array;
    }

}
