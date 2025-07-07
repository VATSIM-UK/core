<?php

namespace App\Models\Cts;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticalExaminers extends Model
{
    protected $connection = 'cts';

    protected $table = 'practical_examiners';

    public $timestamps = false;

    public function exam(): BelongsTo
    {
        return $this->belongsTo(ExamBooking::class, 'examid', 'id');
    }

    public function primaryExaminer(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'senior', 'id');
    }
}
