<?php

namespace App\Services\Training;

use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalExaminers;
use App\Models\Mship\Account;
use App\Notifications\Training\Exams\ExamCancelledByExaminerStudentNotification;
use App\Notifications\Training\Exams\ExamCancelledExaminerNotification;
use App\Notifications\Training\Exams\ExamCancelledStudentNotification;
use App\Notifications\Training\Exams\ExamSessionCancelledForCoExaminerNotification;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CancelPendingExamService
{
    public function cancelByStudent(ExamBooking $examBooking, string $reason, Account $cancelledBy): void
    {
        $this->prepareBooking($examBooking);
        $this->assertStudentMayCancel($examBooking, $cancelledBy);

        $bookingForNotifications = $this->snapshotBookingForNotifications($examBooking);

        $this->executeCancellation($examBooking, $reason, $cancelledBy, function () use ($bookingForNotifications, $reason, $cancelledBy): void {
            $this->notifyStudentInitiatedCancellation($bookingForNotifications, $reason, $cancelledBy);
        });
    }

    public function cancelByExaminer(ExamBooking $examBooking, string $reason, Account $cancelledBy): void
    {
        $this->prepareBooking($examBooking);
        $this->assertExaminerMayCancel($examBooking, $cancelledBy);

        $bookingForNotifications = $this->snapshotBookingForNotifications($examBooking);

        $this->executeCancellation($examBooking, $reason, $cancelledBy, function () use ($bookingForNotifications, $cancelledBy): void {
            $this->notifyExaminerInitiatedCancellation($bookingForNotifications, $cancelledBy);
        });
    }

    private function prepareBooking(ExamBooking $examBooking): void
    {
        $examBooking->loadMissing(['student', 'examiners.primaryExaminer', 'examiners.secondaryExaminer', 'examiners.traineeExaminer']);
    }

    private function assertStudentMayCancel(ExamBooking $examBooking, Account $cancelledBy): void
    {
        if ((int) $examBooking->student->cid !== (int) $cancelledBy->id) {
            throw new AuthorizationException('You may not cancel this exam booking.');
        }
    }

    private function assertExaminerMayCancel(ExamBooking $examBooking, Account $cancelledBy): void
    {
        if (! $this->isAssignedExaminer($examBooking, $cancelledBy)) {
            throw new AuthorizationException('You may not cancel this exam booking.');
        }

        $exam = Str::lower((string) $examBooking->exam);
        if ($exam === '' || ! $cancelledBy->can("training.exams.conduct.{$exam}")) {
            throw new AuthorizationException('You do not have permission to cancel this exam.');
        }
    }

    private function executeCancellation(ExamBooking $examBooking, string $reason, Account $cancelledBy, Closure $sendNotifications): void
    {
        DB::connection('cts')->transaction(function () use ($examBooking, $reason, $cancelledBy): void {
            DB::connection('cts')->table('cancel_reason')->insert([
                'sesh_id' => $examBooking->id,
                'sesh_type' => 'EX',
                'reason' => $reason,
                'used' => 0,
                'reason_by' => $cancelledBy->id,
                'date' => now(),
            ]);

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

        DB::connection('cts')->afterCommit(fn () => $sendNotifications());
    }

    private function snapshotBookingForNotifications(ExamBooking $examBooking): ExamBooking
    {
        $snapshot = $examBooking->replicate();
        $snapshot->id = $examBooking->id;
        $snapshot->exists = true;
        $snapshot->setRelation('student', $examBooking->student);
        $snapshot->setRelation('examiners', $examBooking->examiners);

        return $snapshot;
    }

    private function isAssignedExaminer(ExamBooking $examBooking, Account $cancelledBy): bool
    {
        $member = $cancelledBy->member;
        if (! $member || ! $examBooking->examiners) {
            return false;
        }

        $pe = $examBooking->examiners;

        return collect([$pe->senior, $pe->other, $pe->trainee])
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->contains($member->id);
    }

    private function notifyStudentInitiatedCancellation(ExamBooking $examBooking, string $reason, Account $cancelledBy): void
    {
        $examinerAccount = $examBooking->examiners?->primaryExaminer->account;

        $cancelledBy->notify(new ExamCancelledStudentNotification($examBooking));
        $examinerAccount?->notify(new ExamCancelledExaminerNotification($examBooking, $reason));
    }

    private function notifyExaminerInitiatedCancellation(ExamBooking $examBooking, Account $cancelledBy): void
    {
        $studentAccount = $examBooking->studentAccount();
        if ($studentAccount) {
            $studentAccount->notify(new ExamCancelledByExaminerStudentNotification($examBooking, $cancelledBy));
        }

        $pe = $examBooking->examiners;
        if (! $pe) {
            return;
        }

        $memberIds = collect([$pe->senior, $pe->other, $pe->trainee])
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->unique()
            ->values();

        foreach ($memberIds as $memberId) {
            $member = Member::find($memberId);
            $account = $member?->account;
            if (! $account || (int) $account->id === (int) $cancelledBy->id) {
                continue;
            }

            $account->notify(new ExamSessionCancelledForCoExaminerNotification($examBooking, $cancelledBy));
        }
    }
}
