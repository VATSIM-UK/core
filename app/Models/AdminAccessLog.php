<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminAccessLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function accessor()
    {
        return $this->belongsTo(User::class, 'accessor_account_id');
    }

    public function loggable()
    {
        return $this->morphTo();
    }
}
