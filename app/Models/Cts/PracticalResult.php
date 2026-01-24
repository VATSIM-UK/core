<?php

namespace App\Models\Cts;

use App\Enums\ExamResultEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticalResult extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    public $timestamps = false;

    protected $casts = [
        'date' => 'datetime',
        'result' => ExamResultEnum::class,
    ];

    public $guarded = [];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'student_id', 'id');
    }

    public function examBooking(): BelongsTo
    {
        return $this->belongsTo(ExamBooking::class, 'examid', 'id');
    }

    public function resultHuman(): string
    {
        return $this->result->human();
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(ExamCriteriaAssessment::class, 'examid', 'examid')->with('examCriteria');
    }
}
