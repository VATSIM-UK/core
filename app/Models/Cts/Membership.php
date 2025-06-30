<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Membership extends Model
{
    use HasFactory;
    
    protected $connection = 'cts';

    protected $guarded = [];

    public $timestamps = false;

    public $incrementing = false;

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }
}
