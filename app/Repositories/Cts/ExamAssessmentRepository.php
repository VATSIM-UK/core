<?php

namespace App\Repositories\Cts;

use App\Models\Cts\ExamCriteriaAssessment;

class ExamAssessmentRepository
{
    public function upsertExamCriteriaAssessment(
        int $examId,
        int $criteriaId,
        string $grade,
        ?string $comments = null
    ): void {
        ExamCriteriaAssessment::updateOrCreate(
            [
                'examid' => $examId,
                'criteria_id' => $criteriaId,
            ],
            [
                'examid' => $examId,
                'criteria_id' => $criteriaId,
                'result' => $grade,
                // explicitly using empty string as the column is not nullable
                'notes' => $comments ?? '',
                'addnotes' => $comments ? true : false,
            ]
        );
    }
}
