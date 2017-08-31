<?php

namespace App\Traits;

use App\Models\Sys\Data\Change;

trait RecordsDataChanges
{
    public function recordChanges($model)
    {
        foreach ($model->getDirty() as $attribute => $value) {
            if ($this->shouldWeIgnore($attribute)) {
                continue;
            }

            $original = $model->getOriginal($attribute);

            Change::create([
                'model_id' => $model->getKey(),
                'model_type' => get_class($model),
                'data_key' => $attribute,
                'data_old' => $original,
                'data_new' => $value,
            ]);
        }

        return $model;
    }

    private function shouldWeIgnore($attribute)
    {
        return in_array($attribute, $this->getDoNotTrack());
    }

    private function getDoNotTrack()
    {
        if (isset($this->doNotTrack) && is_array($this->doNotTrack)) {
            return $this->getDoNotTrack;
        }

        return [];
    }
}
