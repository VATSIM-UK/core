<?php

namespace App\Services\Training;

use App\Enums\TheoryExamReminderStatus;
use App\Models\Cts\Member;
use App\Models\Cts\TheoryResult;
use App\Models\Training\Seminar\Seminar;
use App\Models\Training\WaitingList\Removal;
use App\Models\Training\WaitingList\RemovalReason;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Models\Training\WaitingList\WaitingListTheoryReminder;
use App\Notifications\Training\SeminarTheoryExamReminderNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SeminarTheoryExamReminderService
{
    public function sendReminder(WaitingListAccount $waitingListAccount, ?Seminar $seminar = null): ?WaitingListTheoryReminder
    {
        $waitingList = $waitingListAccount->waitingList;

        if (! $waitingList?->cts_theory_exam_level) {
            return null;
        }

        $alreadyReminded = WaitingListTheoryReminder::query()
            ->where('waiting_list_account_id', $waitingListAccount->id)
            ->exists();

        if ($alreadyReminded) {
            return null;
        }

        return DB::transaction(function () use ($waitingListAccount, $seminar): WaitingListTheoryReminder {
            $reminder = WaitingListTheoryReminder::create([
                'waiting_list_account_id' => $waitingListAccount->id,
                'account_id' => $waitingListAccount->account_id,
                'seminar_id' => $seminar?->id,
                'status' => TheoryExamReminderStatus::Pending->value,
                'reminded_at' => now(),
                'last_attempt_at' => $this->latestAttemptAt($waitingListAccount),
                'next_check_at' => now()->addDays(WaitingListTheoryReminder::COMPLETION_WINDOW_DAYS),
            ]);

            $waitingListAccount->account->notify(new SeminarTheoryExamReminderNotification($reminder));

            return $reminder;
        });
    }

    public function checkDueReminders(): void
    {
        $reminders = WaitingListTheoryReminder::query()
            ->pending()
            ->due()
            ->with(['waitingListAccount.waitingList', 'waitingListAccount.account'])
            ->get();

        foreach ($reminders as $reminder) {
            $this->checkReminder($reminder);
        }
    }

    private function checkReminder(WaitingListTheoryReminder $reminder): void
    {
        $waitingListAccount = $reminder->waitingListAccount;

        if (! $waitingListAccount) {
            // They have left the waiting list for another reason, so there is nothing left for us to check.
            $reminder->update([
                'status' => TheoryExamReminderStatus::Cancelled->value,
                'resolved_at' => now(),
            ]);

            return;
        }

        if ($waitingListAccount->theory_exam_passed) {
            $reminder->update([
                'status' => TheoryExamReminderStatus::Passed->value,
                'resolved_at' => now(),
            ]);

            return;
        }

        $latestAttempt = $this->latestAttemptAt($waitingListAccount);
        $lastKnownAttempt = $reminder->last_attempt_at ?? $reminder->reminded_at;
        $deadline = $reminder->next_check_at;

        $attemptedInsideWindow = $latestAttempt && $latestAttempt->greaterThan($lastKnownAttempt) && $latestAttempt->lessThanOrEqualTo($deadline);

        if ($attemptedInsideWindow) {
            // They sat (and failed) the exam inside the window, so they get a cool down before we check on them again.
            $reminder->update([
                'last_attempt_at' => $latestAttempt,
                'next_check_at' => $latestAttempt->copy()->addDays(WaitingListTheoryReminder::FAILED_ATTEMPT_RECHECK_WINDOW_DAYS),
            ]);

            return;
        }

        $this->removeFromWaitingList($reminder, $waitingListAccount);
    }

    private function removeFromWaitingList(WaitingListTheoryReminder $reminder, WaitingListAccount $waitingListAccount): void
    {
        $waitingList = $waitingListAccount->waitingList;

        if ($waitingList && $waitingList->includesAccount($waitingListAccount->account_id)) {
            $waitingList->removeFromWaitingList(
                $waitingListAccount->account,
                new Removal(RemovalReason::SeminarTheoryExamNotAttempted, null)
            );
        }

        $reminder->update([
            'status' => TheoryExamReminderStatus::Removed->value,
            'resolved_at' => now(),
        ]);
    }

    private function latestAttemptAt(WaitingListAccount $waitingListAccount): ?Carbon
    {
        $examLevel = $waitingListAccount->waitingList?->cts_theory_exam_level;

        if (! $examLevel) {
            return null;
        }

        $memberId = Member::query()
            ->where('cid', $waitingListAccount->account_id)
            ->value('id');

        if (! $memberId) {
            return null;
        }

        $latestAttempt = TheoryResult::query()
            ->where('student_id', $memberId)
            ->where('exam', $examLevel)
            ->where('submitted', 1)
            ->max('started');

        return $latestAttempt ? Carbon::parse($latestAttempt) : null;
    }
}
