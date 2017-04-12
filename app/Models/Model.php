<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class Model extends EloquentModel
{
    protected $doNotTrack = [];

    protected static function boot()
    {
        parent::boot();
        self::created([get_called_class(), 'eventCreated']);
        self::updated([get_called_class(), 'eventUpdated']);
        self::deleted([get_called_class(), 'eventDeleted']);
    }

    public static function eventCreated($model)
    {
    }

    public static function eventUpdated($model)
    {
    }

    public static function eventDeleted($model)
    {
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['status'] = ($this->deleted_at ? 'Deleted' : 'Active');
        if (isset($array['pivot'])) {
            unset($array['pivot']);
        }

        return $array;
    }

    public function save(array $options = [])
    {
        // Let's check the old data vs new data, so we can store data changes!
        // We check for the presence of the dataChanges relationship, to warrent tracking changes.
        if (get_called_class() != \App\Models\Sys\Data\Change::class && method_exists($this, 'dataChanges')) {
            // Get the changed values!
            foreach ($this->getDirty() as $attribute => $value) {
                // There are some values we might want to remove.  They may be stored in a variable
                // called doNotTrack
                if (isset($this->doNotTrack) && is_array($this->doNotTrack)) {
                    if (in_array($attribute, $this->doNotTrack)) {
                        continue; // We don't wish to track this :(
                    }
                }

                $original = $this->getOriginal($attribute);

                $dataChange = new \App\Models\Sys\Data\Change();
                $dataChange->data_key = $attribute;
                $dataChange->data_old = $original;
                $dataChange->data_new = $value;
                $this->dataChanges()->save($dataChange);
            }
        }

        return parent::save($options);
    }
}
