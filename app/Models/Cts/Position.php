<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    public $timestamps = false;

    protected $fillable = [
        'rts_id',
        'callsign',
        'name',
        'rating',
        'auto_rating',
        'vis_roster',
        'anon_requests',
        'prog_sheet_id',
        'prog_sheet_assign_by',
    ];
}
