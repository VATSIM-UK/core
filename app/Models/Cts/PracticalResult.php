<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticalResult extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    public $timestamps = false;

    public const PASSED = 'P';

    public const FAILED = 'F';

    public const INCOMPLETE = 'N';

    protected $casts = [
        'date' => 'datetime',
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
        return match ($this->result) {
            self::PASSED => 'Passed',
            self::FAILED => 'Failed',
            self::INCOMPLETE => 'Incomplete',
            default => 'Unknown',
        };
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(ExamCriteriaAssessment::class, 'examid', 'examid')->with('examCriteria');
    }
}
