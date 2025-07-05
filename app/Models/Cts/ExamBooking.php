<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;

class ExamBooking extends Model
{
    protected $connection = 'cts';
    protected $table = 'exam_book';
    public $timestamps = false;

    public function student()
    {
        return $this->belongsTo(Member::class, 'student_id', 'id');
    }

    #[Scope]
    protected function conductable()
    {
        return $this->where('taken', 1)
            ->where('finished', 0);
    }
}
