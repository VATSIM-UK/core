<?php

namespace App\Email;

use App\Models\Model;

class Event extends Model
{
    protected $table = 'email_events';
    protected $dates = ['triggered_at'];
    protected $guarded = ['id'];
    public $timestamps = false;
}
