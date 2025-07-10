<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PracticalResult extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    public $timestamps = false;

    public const PASSED = 'P';

    public const FAILED = 'F';

    protected $casts = [
        'date' => 'datetime',
    ];

    public $guarded = [];
}
