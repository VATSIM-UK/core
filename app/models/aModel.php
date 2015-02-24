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
        \Cache::tags(get_class($model), $model->getTable())->flush();
        return;
    }

    public static function eventUpdated($model) {
        \Cache::tags(get_class($model), $model->getTable())->flush();
        return;
    }

    public static function eventDeleted($model) {
        \Cache::tags(get_class($model), $model->getTable())->flush();
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

    public function save(array $options = []) {
        // Let's check the old data vs new data, so we can store data changes!
        if (get_called_class() != "Models\Sys\Data\Change" && method_exists($this, "dataChanges")) {
            // Get the changed values!
            foreach ($this->getDirty() as $attribute => $value) {
                $original = $this->getOriginal($attribute);

                $dataChange = new \Models\Sys\Data\Change();
                $dataChange->data_key = $attribute;
                $dataChange->data_old = $original;
                $dataChange->data_new = $value;
                $this->dataChanges()->save($dataChange);
            }
        }

        return parent::save($options);
    }

}
