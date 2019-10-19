<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Model;

class Validation extends Model
{
    protected $connection = 'cts';
    protected $guarded = [];

    public $timestamps = false;
}
