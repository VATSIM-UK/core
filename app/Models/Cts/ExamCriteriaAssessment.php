<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Model;

class ExamCriteriaAssessment extends Model
{
    protected $table = 'practical_criteria_assess';

    protected $connection = 'cts';

    public $timestamps = false;

    public $guarded = [];
}
