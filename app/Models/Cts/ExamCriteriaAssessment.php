<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamCriteriaAssessment extends Model
{
    protected $table = 'practical_criteria_assess';

    protected $connection = 'cts';

    public $timestamps = false;

    public $guarded = [];

    public const string FULLY_COMPETENT = 'P';

    public const string MOSTLY_COMPETENT = 'M';

    public const string PARTLY_COMPETENT = 'R';

    public const string NOT_ASSESSED = 'N';

    public const string FAIL = 'F';

    public static function gradeDropdownOptions(): array
    {
        return [
            self::FULLY_COMPETENT => 'Fully Competent',
            self::MOSTLY_COMPETENT => 'Mostly Competent',
            self::PARTLY_COMPETENT => 'Partly Competent',
            self::NOT_ASSESSED => 'Not Assessed',
            self::FAIL => 'Fail',
        ];
    }

    public function examCriteria(): BelongsTo
    {
        return $this->belongsTo(ExamCriteria::class, 'criteria_id', 'id');
    }

    public static function resultHuman(?string $result): string
    {
        return match ($result) {
            self::FULLY_COMPETENT => 'Fully Competent',
            self::MOSTLY_COMPETENT => 'Mostly Competent',
            self::PARTLY_COMPETENT => 'Partly Competent',
            self::NOT_ASSESSED => 'Not Assessed',
            self::FAIL => 'Fail',
        };
    }
}
