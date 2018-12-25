<?php

namespace App\Models;

use App\Models\Concerns\TracksEvents;
use App\Models\Concerns\TracksChanges;
use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * App\Models\Model.
 */
abstract class Model extends Eloquent
{
    use TracksChanges, TracksEvents;
}
