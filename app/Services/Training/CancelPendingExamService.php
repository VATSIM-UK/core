<?php

namespace App\Services\Training;

use App\Models\Cts\ExamBooking;
use App\Models\Cts\PracticalExaminers;
use App\Models\Mship\Account;
use App\Notifications\Training\Exams\ExamCancelledExaminerNotification;
use App\Notifications\Training\Exams\ExamCancelledStudentNotification;
use Illuminate\Support\Facades\DB;

class CancelPendingExamService
{
    public function cancel(ExamBooking $examBooking, string $reason, Account $cancelledBy): void
    {
        DB::transaction(function () use ($examBooking, $reason, $cancelledBy): void {
            DB::connection('cts')->table('cancel_reason')->insert([
                'sesh_id' => $examBooking->id,
                'sesh_type' => 'EX',
                'reason' => $reason,
                'used' => 0,
                'reason_by' => $cancelledBy->id,
                'date' => now(),
            ]);

            $examinerAccount = $examBooking->examiners?->primaryExaminer->account;

            $cancelledBy->notify(new ExamCancelledStudentNotification($examBooking, $reason));
            $examinerAccount?->notify(new ExamCancelledExaminerNotification($examBooking, $reason));

            $examBooking->update([
                'taken' => 0,
                'taken_date' => null,
                'taken_from' => null,
                'taken_to' => null,
                'exmr_id' => null,
                'exmr_rating' => null,
                'time_book' => null,
            ]);

            $examBooking->setup->update([
                'booked' => 0,
            ]);

            PracticalExaminers::where('examid', $examBooking->id)->delete();
        });
    }
}
