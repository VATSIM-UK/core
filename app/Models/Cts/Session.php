<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $connection = 'cts';

    const UPDATED_AT = null;
    const CREATED_AT = 'request_time';
}
