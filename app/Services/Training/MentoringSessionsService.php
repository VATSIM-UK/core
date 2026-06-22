<?php

namespace App\Services\Training;

use App\Models\Cts\Availability;
use App\Models\Cts\CancelReason;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Notifications\Training\Mentoring\MentoringSessionAcceptedMentorNotification;
use App\Notifications\Training\Mentoring\MentoringSessionAcceptedStudentNotification;
use App\Notifications\Training\Mentoring\MentoringSessionCancelledMentorNotification;
use App\Notifications\Training\Mentoring\MentoringSessionCancelledStudentNotification;
use App\Notifications\Training\Mentoring\MentoringSessionReallocatedNewMentorNotification;
use App\Notifications\Training\Mentoring\MentoringSessionReallocatedOldMentorNotification;
use App\Notifications\Training\Mentoring\MentoringSessionReallocatedStudentNotification;
use App\Notifications\Training\Mentoring\MentoringSessionRescheduledMentorNotification;
use App\Notifications\Training\Mentoring\MentoringSessionRescheduledStudentNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

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

            $this->validateSessionTimes($availability, $takenFrom, $takenTo);

            if ($mentorAccount->cannot('accept', $session)) {
                throw new AuthorizationException('You are not authorized to accept mentoring sessions for this position.');
            }

            $session->update([
                'mentor_id' => $mentorMember->id,
                'mentor_rating' => $mentorAccount->qualification_atc?->vatsim,
                'taken' => 1,
                'taken_date' => $availability->date,
                'taken_from' => $takenFrom,
                'taken_to' => $takenTo,
            ]);

            DB::afterCommit(function () use ($session) {
                $this->notifyParticipants($session, 'accepted');
            });

            return true;
        });
    }

    /**
     * Reschedules an existing session to a new availability slot.
     */
    public function rescheduleSession(int $sessionId, int $newAvailabilityId, string $takenFrom, string $takenTo, Account $userAccount): bool
    {
        $session = Session::findOrFail($sessionId);
        $availability = Availability::findOrFail($newAvailabilityId);

        if ($userAccount->cannot('reschedule', $session)) {
            throw new AuthorizationException('You are not authorized to reschedule this session.');
        }

        if ($availability->student_id !== $session->student_id) {
            throw new InvalidArgumentException("The selected availability does not belong to the session's student.");
        }

        $this->validateSessionTimes($availability, $takenFrom, $takenTo);

        return DB::transaction(function () use ($session, $availability, $takenFrom, $takenTo) {
            $previousDateTime = $session->formattedSessionDateTime();

            $session->update([
                'taken_date' => $availability->date,
                'taken_from' => $takenFrom,
                'taken_to' => $takenTo,
            ]);

            DB::afterCommit(function () use ($session, $previousDateTime) {
                $this->notifyParticipants($session, 'rescheduled', [
                    'previousDateTime' => $previousDateTime,
                ]);
            });

            return true;
        });
    }

    /**
     * Cancels an existing mentoring session and logs the reason.
     */
    public function cancelSession(int $sessionId, string $reason, Account $cancellerAccount): bool
    {
        $session = Session::findOrFail($sessionId);

        if ($cancellerAccount->cannot('cancel', $session)) {
            throw new AuthorizationException('You are not authorized to cancel this session.');
        }

        return DB::transaction(function () use ($session, $reason, $cancellerAccount) {
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

            DB::afterCommit(function () use ($session, $reason, $cancellerAccount) {
                $this->notifyParticipants($session, 'cancelled', [
                    'reason' => $reason,
                    'cancellerAccount' => $cancellerAccount,
                ]);
            });

            return true;
        });
    }

    /**
     * Re-allocates an existing mentoring session to a new mentor.
     */
    public function reallocateSession(int $sessionId, int $newMentorCid, Account $userAccount, string $reason): bool
    {
        $session = Session::findOrFail($sessionId);

        if ($userAccount->cannot('reallocate', $session)) {
            throw new AuthorizationException('You are not authorized to reallocate this session.');
        }

        $newMentorMember = Member::where('cid', $newMentorCid)->firstOrFail();
        $newMentorAccount = Account::findOrFail($newMentorCid);

        if (! $newMentorAccount->canMentorPosition($session->position)) {
            throw new AuthorizationException('The selected mentor does not have permission to mentor this position.');
        }

        $oldMentorAccount = $session->mentorAccount();

        $session->update([
            'mentor_id' => $newMentorMember->id,
        ]);

        $this->notifyParticipants($session, 'reallocated', [
            'oldMentorAccount' => $oldMentorAccount,
            'newMentorAccount' => $newMentorAccount,
            'reason' => $reason,
        ]);

        return true;
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

            case 'reallocated':
                $oldMentorAccount = $data['oldMentorAccount'];
                $newMentorAccount = $data['newMentorAccount'];
                $reason = $data['reason'];
                $oldMentorName = $oldMentorAccount?->name ?? 'Unknown';

                if ($studentAccount) {
                    $studentAccount->notify(new MentoringSessionReallocatedStudentNotification($session, $oldMentorName));
                }

                if ($oldMentorAccount) {
                    $newMentorName = $newMentorAccount->name ?? 'Unknown';
                    $oldMentorAccount->notify(new MentoringSessionReallocatedOldMentorNotification($session, $reason, $newMentorName));
                }

                $newMentorAccount->notify(new MentoringSessionReallocatedNewMentorNotification($session, $reason));
                break;

            default:
                throw new Exception("Unknown notification action: {$action}");
        }
    }

    public function checkForOverlappingBookings(string $callsign, string $date, string $takenFrom, string $takenTo, ?int $ignoreSessionId = null): Session|ExamBooking|null
    {
        $overlappingSession = Session::query()
            ->where('position', $callsign)
            ->whereDate('taken_date', $date)
            ->where('taken_from', '<', $takenTo)
            ->where('taken_to', '>', $takenFrom)
            ->whereNull('cancelled_datetime')
            ->when($ignoreSessionId, function ($query, $ignoreSessionId) {
                $query->where('id', '!=', $ignoreSessionId);
            })
            ->first();

        if ($overlappingSession) {
            return $overlappingSession;
        }

        return ExamBooking::query()
            ->where('position_1', $callsign)
            ->whereDate('taken_date', $date)
            ->where('taken_from', '<', $takenTo)
            ->where('taken_to', '>', $takenFrom)
            ->where('taken', 1)
            ->where('finished', ExamBooking::NOT_FINISHED_FLAG)
            ->first();
    }

    private function validateSessionTimes(Availability $availability, string $takenFrom, string $takenTo): void
    {
        $sessionStart = Carbon::parse($availability->date)->setTimeFromTimeString($takenFrom);

        if ($sessionStart->isPast()) {
            throw new InvalidArgumentException('Cannot accept a mentoring session that is in the past.');
        }

        if (strtotime($takenTo) <= strtotime($takenFrom)) {
            throw new InvalidArgumentException('The session end time must be after the start time.');
        }

        $requestedStart = strtotime($takenFrom);
        $requestedEnd = strtotime($takenTo);
        $availabilityStart = strtotime($availability->from);
        $availabilityEnd = strtotime($availability->to);

        if ($requestedStart < $availabilityStart || $requestedEnd > $availabilityEnd) {
            throw new InvalidArgumentException("The requested times fall outside the student's availability window.");
        }
    }
}
