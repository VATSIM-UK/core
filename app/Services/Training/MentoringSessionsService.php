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

class MentoringSessionsService
{
    /**
     * Assigns a mentor to a student's pending session based on an availability slot.
     *
     * @param  int|string  $availabilityId
     * @param  int|string  $mentorId
     * @return bool True if a session was found and successfully updated, false otherwise.
     */
    public function acceptSession($availabilityId, $mentorId, string $takenFrom, string $takenTo): bool
    {
        $availability = Availability::find($availabilityId);

        if (! $availability) {
            return false;
        }

        $pendingSession = Session::query()
            ->where('student_id', $availability->student_id)
            ->whereNull('mentor_id')
            ->whereNull('filed')
            ->whereNull('cancelled_datetime')
            ->first();

        if (! $pendingSession) {
            return false;
        }

        $mentorMember = Member::find($mentorId);
        $mentor = Account::find($mentorMember->cid);

        if (! $mentor->can('mentorPosition', [Session::class, $pendingSession->position])) {
            return false;
        }

        if (! $this->isTimeRangeValid($availability, $takenFrom, $takenTo)) {
            return false;
        }

        $mentorRating = $mentor?->qualification_atc?->vatsim;
        $updated = $pendingSession->update([
            'mentor_id' => $mentorMember->id,
            'mentor_rating' => $mentorRating,
            'taken_date' => Carbon::parse($availability->date)->format('Y-m-d'),
            'taken_from' => $takenFrom,
            'taken_to' => $takenTo,
            'taken' => 1,
        ]);

        if (! $updated) {
            return false;
        }

        $this->notifyAccepted($pendingSession->fresh());

        return true;
    }

    /**
     * Reschedules an existing session based on a new availability slot.
     *
     * @param  int|string  $sessionId
     * @param  int|string  $availabilityId
     */
    public function rescheduleSession($sessionId, $availabilityId, string $takenFrom, string $takenTo): bool
    {
        $session = Session::find($sessionId);
        $availability = Availability::find($availabilityId);

        if (! $session || ! $availability) {
            return false;
        }

        if (! $this->isTimeRangeValid($availability, $takenFrom, $takenTo)) {
            return false;
        }

        $previousDateTime = "{$session->taken_date} {$session->taken_from}";
        $updated = $session->update([
            'taken_date' => Carbon::parse($availability->date)->format('Y-m-d'),
            'taken_from' => $takenFrom,
            'taken_to' => $takenTo,
        ]);

        if (! $updated) {
            return false;
        }

        $this->notifyRescheduled($session->fresh(), $previousDateTime);

        return true;
    }

    /**
     * Cancels an existing session and logs the reason.
     *
     * @param  int|string  $sessionId
     * @param  int|string  $cancellerMemberId
     */
    public function cancelSession($sessionId, string $reason, $cancellerMemberId): bool
    {
        $session = Session::find($sessionId);
        $cancellerMember = Member::find($cancellerMemberId);

        $cancelledBy = Account::find($cancellerMember->cid);

        $updated = $session->update([
            'cancelled_datetime' => now(),
        ]);

        if (! $updated) {
            return false;
        }

        CancelReason::create([
            'sesh_id' => $session->id,
            'sesh_type' => 'ME',
            'reason' => $reason,
            'reason_by' => $cancellerMemberId,
        ]);

        Session::create([
            'rts_id' => $session->rts_id,
            'position' => $session->position,
            'progress_sheet_id' => $session->progress_sheet_id,
            'student_id' => $session->student_id,
            'student_rating' => $session->student_rating,
            'request_time' => Carbon::now(),
        ]);

        $this->notifyCancelled($session, $reason, $cancelledBy);

        return true;
    }

    private function isTimeRangeValid(Availability $availability, string $takenFrom, string $takenTo): bool
    {
        $availabilityStart = Carbon::parse($availability->from);
        $availabilityEnd = Carbon::parse($availability->to);
        $takenFromTime = Carbon::parse($takenFrom);
        $takenToTime = Carbon::parse($takenTo);

        if ($takenFromTime->greaterThanOrEqualTo($takenToTime)) {
            return false;
        }

        if ($takenFromTime->lessThan($availabilityStart) || $takenToTime->greaterThan($availabilityEnd)) {
            return false;
        }

        return true;
    }

    private function notifyAccepted(Session $session): void
    {
        $session->studentAccount()?->notify(
            new MentoringSessionAcceptedStudentNotification($session),
        );

        $session->mentorAccount()->notify(new MentoringSessionAcceptedMentorNotification($session));
    }

    private function notifyCancelled(Session $session, string $reason, Account $cancelledBy): void
    {
        $session->studentAccount()?->notify(
            new MentoringSessionCancelledStudentNotification($session, $cancelledBy, $reason),
        );

        $cancelledBy->notify(new MentoringSessionCancelledMentorNotification($session, $reason));
    }

    private function notifyRescheduled(Session $session, string $previousDateTime): void
    {
        $session->studentAccount()?->notify(
            new MentoringSessionRescheduledStudentNotification($session, $previousDateTime),
        );

        $session->mentorAccount()?->notify(
            new MentoringSessionRescheduledMentorNotification($session, $previousDateTime),
        );
    }
}
