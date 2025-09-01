<?php

namespace App\Repositories\Cts;

use App\Events\Training\Exams\PracticalExamCompleted;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\PracticalResult;
use Illuminate\Support\Collection;

class ExamResultRepository
{
    public function getRecentPassedExamsOfType(string $type, int $daysConsideredRecent = 3): Collection
    {
        return PracticalResult::where('result', PracticalResult::PASSED)
            ->where('exam', $type)
            ->where('date', '>=', now()->subDays($daysConsideredRecent))
            ->get();
    }

    public function getPendingExamsOfType(string $type, int $daysConsideredRecent = 180): Collection
    {
        return ExamBooking::where('exam', $type)
            ->where('taken', 1)
            ->where('finished', ExamBooking::NOT_FINISHED_FLAG)
            ->get();
    }

    public function createPracticalResult(ExamBooking $examBooking, string $result, ?string $additionalComments)
    {
        $practicalResult = PracticalResult::create([
            'examid' => $examBooking->id,
            'student_id' => $examBooking->student->id,
            'result' => $result,
            'notes' => $additionalComments ?? '',
            'date' => now(),
            'exam' => $examBooking->exam,
        ]);

        $examBooking->update(['finished' => ExamBooking::FINISHED_FLAG]);

        event(new PracticalExamCompleted($examBooking, $practicalResult));
    }
}
