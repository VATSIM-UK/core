<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $connection = 'cts';

    protected $table = 'reminders';

    protected $guarded = [];

    public $timestamps = false;

    protected $attributes = [
        'sesh_type' => '',
        'who' => '',
        'reminder' => '',
        'set' => 0,
        'sent' => 0,
    ];

    public function scopeExams($query)
    {
        return $query->where('sesh_type', 'E');
    }

    public function scopeMentoring($query)
    {
        return $query->where('sesh_type', 'M');
    }
}
