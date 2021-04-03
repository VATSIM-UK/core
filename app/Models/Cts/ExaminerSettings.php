<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Model;

class ExaminerSettings extends Model
{
    protected $connection = 'cts';
    protected $table = 'examinerSettings';

    public $timestamps = false;

    public function member()
    {
        return $this->belongsTo(Member::class, 'memberID', 'id');
    }
}
