<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class RosterUpdate extends Model
{
    use HasFactory;

    protected $fillable = ['period_start', 'period_end', 'data'];

    protected $casts = [
        'data' => 'json',
    ];
}
