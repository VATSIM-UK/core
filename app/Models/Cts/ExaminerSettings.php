<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExaminerSettings extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'examinerSettings';

    public $timestamps = false;

    public function member()
    {
        return $this->belongsTo(Member::class, 'memberID', 'id');
    }

    public function scopeAtc($query)
    {
        return $query->where('OBS', '=', 1) // OBS to S1 examiner
            ->orWhere('S1', '=', 1) // S1 to S2 examiner
            ->orWhere('S2', '=', 1) // S2 to S3 examiner
            ->orWhere('S3', '=', 1); // S3 to C1 examiner
    }

    public function scopePilot($query)
    {
        return $query->where('P1', '=', 1)
            ->orWhere('P2', '=', 1)
            ->orWhere('P3', '=', 1)
            ->orWhere('P4', '=', 1)
            ->orWhere('P5', '=', 1);
    }
}
