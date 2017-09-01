<?php

namespace App\Models;

use App\Models\Concerns\TracksChanges;
use App\Models\Concerns\TracksEvents;
use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * App\Models\Model
 */
abstract class Model extends Eloquent
{
    use TracksChanges, TracksEvents;

    public function toArray()
    {
        $array = parent::toArray();
        $array['status'] = ($this->deleted_at ? 'Deleted' : 'Active');
        if (isset($array['pivot'])) {
            unset($array['pivot']);
        }

        return $array;
    }
}
