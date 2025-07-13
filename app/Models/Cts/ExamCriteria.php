<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ExamCriteria extends Model
{
    protected $connection = 'cts';

    protected $table = 'exam_criteria';

    public $timestamps = false;

    protected $guarded = [];

    #[Scope]
    protected function byType(Builder $query, string $exam)
    {
        return $query->where('exam', $exam)->where('deleted', 0);
    }
}
