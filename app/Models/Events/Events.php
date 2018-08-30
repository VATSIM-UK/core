<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    protected $fillable = ['name', 'description', 'task_date'];
}
