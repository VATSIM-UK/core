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

    private static function queueEmail($area_key, $action){
        $area = substr($area_key, 0, strpos($area_key, "_"));
        $key = substr($area_key, (strpos($area_key, "_") == 0 ? 0 : strpas($area_key, "_")));

        Queue::push("PostMasterDispatch", array("area" => $area, "key" => $key, "action" => $action));
    }

    public static function eventCreated($model) {
        $this->queueEmail($model->getTable(), "CREATED");
    }

    public static function eventUpdated($model) {
        $this->queueEmail($model->getTable(), "UPDATED");
    }

    public static function eventDeleted($model) {
        $this->queueEmail($model->getTable(), "DELETED");
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
