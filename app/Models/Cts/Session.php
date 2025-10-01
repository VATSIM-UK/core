<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    public $timestamps = false;

    public $incrementing = false;

    public function mentor()
    {
        return $this->belongsTo(Member::class, 'mentor_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Member::class, 'student_id', 'id');
    }
}
