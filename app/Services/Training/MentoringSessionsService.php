<?php

namespace App\Services\Training;

use App\Models\Cts\Availability;
use App\Models\Cts\CancelReason;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Notifications\Training\Mentoring\MentoringSessionAcceptedMentorNotification;
use App\Notifications\Training\Mentoring\MentoringSessionAcceptedStudentNotification;
use App\Notifications\Training\Mentoring\MentoringSessionCancelledMentorNotification;
use App\Notifications\Training\Mentoring\MentoringSessionCancelledStudentNotification;
use App\Notifications\Training\Mentoring\MentoringSessionRescheduledMentorNotification;
use App\Notifications\Training\Mentoring\MentoringSessionRescheduledStudentNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class MentoringSessionsService
{
    /**
     * Accepts a pending session by claiming a student's availability slot.
     */
    public function acceptSession(int $availabilityId, Account $mentorAccount, string $takenFrom, string $takenTo): bool
    {
        return DB::transaction(function () use ($availabilityId, $mentorAccount, $takenFrom, $takenTo) {
            $availability = Availability::findOrFail($availabilityId);

            $mentorMember = Member::where('cid', $mentorAccount->id)->firstOrFail();

            $session = Session::query()
                ->where('student_id', $availability->student_id)
                ->whereNull('mentor_id')
                ->whereNull('filed')
                ->whereNull('cancelled_datetime')
                ->first();

            if (! $session) {
                return false;
            }

            $session->update([
                'mentor_id' => $mentorMember->id,
                'mentor_rating' => $mentorAccount->qualification_atc?->vatsim,
                'taken' => 1,
                'taken_date' => $availability->date,
                'taken_from' => $takenFrom,
                'taken_to' => $takenTo,
            ]);

            $this->notifyParticipants($session, 'accepted');

            return true;
        });
    }

    /**
     * Reschedules an existing session to a new availability slot.
     */
    public function rescheduleSession(int $sessionId, int $newAvailabilityId, string $takenFrom, string $takenTo): bool
    {
        return DB::transaction(function () use ($sessionId, $newAvailabilityId, $takenFrom, $takenTo) {
            $session = Session::findOrFail($sessionId);
            $availability = Availability::findOrFail($newAvailabilityId);

            $previousDateTime = $session->formattedSessionDateTime();

            $session->update([
                'taken_date' => $availability->date,
                'taken_from' => $takenFrom,
                'taken_to' => $takenTo,
            ]);

            $this->notifyParticipants($session, 'rescheduled', [
                'previousDateTime' => $previousDateTime,
            ]);

            return true;
        });
    }

    /**
     * Cancels an existing mentoring session and logs the reason.
     */
    public function cancelSession(int $sessionId, string $reason, Account $cancellerAccount): bool
    {
        return DB::transaction(function () use ($sessionId, $reason, $cancellerAccount) {
            $session = Session::findOrFail($sessionId);
            $cancellerMember = Member::where('cid', $cancellerAccount->id)->firstOrFail();

            $session->update([
                'cancelled_datetime' => now(),
            ]);

            CancelReason::create([
                'sesh_id' => $session->id,
                'sesh_type' => 'ME',
                'reason' => $reason,
                'reason_by' => $cancellerMember->id,
            ]);

            Session::create([
                'rts_id' => $session->rts_id,
                'position' => $session->position,
                'progress_sheet_id' => $session->progress_sheet_id,
                'student_id' => $session->student_id,
                'student_rating' => $session->student_rating,
                'request_time' => Carbon::now(),
            ]);

            $this->notifyParticipants($session, 'cancelled', [
                'reason' => $reason,
                'cancellerAccount' => $cancellerAccount,
            ]);

            return true;
        });
    }

    private function notifyParticipants(Session $session, string $action, array $data = []): void
    {
        $studentAccount = $session->studentAccount();
        $mentorAccount = $session->mentorAccount();

        if (! $studentAccount || ! $mentorAccount) {
            return;
        }

        switch ($action) {
            case 'accepted':
                $studentAccount->notify(new MentoringSessionAcceptedStudentNotification($session));
                $mentorAccount->notify(new MentoringSessionAcceptedMentorNotification($session));
                break;

            case 'rescheduled':
                $studentAccount->notify(new MentoringSessionRescheduledStudentNotification($session, $data['previousDateTime']));
                $mentorAccount->notify(new MentoringSessionRescheduledMentorNotification($session, $data['previousDateTime']));
                break;

            case 'cancelled':
                $studentAccount->notify(new MentoringSessionCancelledStudentNotification($session, $data['cancellerAccount'], $data['reason']));
                $mentorAccount->notify(new MentoringSessionCancelledMentorNotification($session, $data['reason']));
                break;

            default:
                throw new Exception("Unknown notification action: {$action}");
        }
    }
}
