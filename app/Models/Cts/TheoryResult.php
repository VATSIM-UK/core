<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheoryResult extends Model
{
    use HasFactory;

    protected $connection = 'cts';
    public $timestamps = false;
}
