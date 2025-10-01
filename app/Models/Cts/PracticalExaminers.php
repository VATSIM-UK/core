<?php

namespace App\Models\Cts;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticalExaminers extends Model
{
    protected $connection = 'cts';

    protected $table = 'practical_examiners';

    public $timestamps = false;

    protected $guarded = [];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(ExamBooking::class, 'examid', 'id');
    }

    public function primaryExaminer(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'senior', 'id');
    }

    public function secondaryExaminer(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'other', 'id');
    }

    public function traineeExaminer(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'trainee', 'id');
    }
}
