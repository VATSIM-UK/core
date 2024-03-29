<?php

namespace App\Repositories\Cts;

use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;

class ExamResultRepository
{
    public function getPassedExamResultsOfTypeForMember(int $cid, string $type)
    {
        $studentId = Member::where('cid', $cid)->firstOrFail()?->id;

        return PracticalResult::where('result', PracticalResult::PASSED)
            ->where('exam', $type)
            ->where('student_id', $studentId)
            ->first();
    }
}
