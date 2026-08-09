<?php

namespace App\Services\Training;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Availability;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Cts\CancelReason;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Notifications\Training\Mentoring\MentoringSessionAcceptedMentorNotification;
use App\Notifications\Training\Mentoring\MentoringSessionAcceptedStudentNotification;
use App\Notifications\Training\Mentoring\MentoringSessionCancelledByStudentNotification;
use App\Notifications\Training\Mentoring\MentoringSessionCancelledMentorNotification;
use App\Notifications\Training\Mentoring\MentoringSessionCancelledStudentConfirmationNotification;
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
     * Creates a mentoring session for a training-place student from an availability slot.
     *
     * Transaction note: `DB::transaction()` only wraps the default (Core) connection.
     * CTS writes (`Session`, CTS bookings) use the `cts` connection and are not rolled
     * back if a later Core write fails. Notifications are deferred with `DB::afterCommit()`
     * so they only run after the Core transaction commits.
     */
    public function createSession(
        TrainingPlace $trainingPlace,
        Availability $availability,
        Account $mentorAccount,
        string $position,
        string $takenFrom,
        string $takenTo,
    ): bool {
        return DB::transaction(function () use ($trainingPlace, $availability, $mentorAccount, $position, $takenFrom, $takenTo) {
            $trainingPlace->loadMissing(['trainable', 'account']);

            $mentorMember = Member::where('cid', $mentorAccount->id)->firstOrFail();
            $studentMember = Member::where('cid', $trainingPlace->account_id)->first();

            if (! $studentMember || $studentMember->id !== $availability->student_id) {
                throw new InvalidArgumentException('The selected availability does not belong to this training place student.');
            }

            $placeCallsigns = $trainingPlace->trainableCtsPositions();

            if (! in_array($position, $placeCallsigns, true)) {
                throw new InvalidArgumentException('The selected position is not valid for this training place.');
            }

            if ($mentorAccount->cannot('create', [Session::class, $position])) {
                throw new AuthorizationException('You are not authorized to create mentoring sessions for this position.');
            }

            if ($trainingPlace->isOnLeaveOfAbsence()) {
                throw new InvalidArgumentException('Cannot create a mentoring session while the student is on leave of absence.');
            }

            $this->validateSessionTimes($availability, $takenFrom, $takenTo);

            $ctsPosition = CtsPosition::query()->where('callsign', $position)->first();

            if (! $ctsPosition) {
                throw new InvalidArgumentException("CTS position not found for callsign [{$position}].");
            }

            $session = Session::query()->create([
                'rts_id' => $ctsPosition->rts_id ?? 0,
                'position' => $ctsPosition->callsign,
                'progress_sheet_id' => $ctsPosition->prog_sheet_id ?? 0,
                'student_id' => $studentMember->id,
                'student_rating' => $studentMember->rating ?? 0,
                'request_time' => now(),
                'mentor_id' => $mentorMember->id,
                'mentor_rating' => $mentorAccount->qualification_atc?->vatsim,
                'taken' => 1,
                'taken_date' => $availability->date,
                'taken_from' => $takenFrom,
                'taken_to' => $takenTo,
                'taken_time' => now(),
            ]);

            DB::afterCommit(function () use ($session) {
                $this->notifyParticipants($session, 'accepted');
            });

            $this->createCoreBooking($session);

            return true;
        });
    }

    /**
     * Accepts a pending session by claiming a student's availability slot.
     *
     * @deprecated Prefer createSession() for Training Panel mentoring.
     */
    public function acceptSession(int $sessionId, int $availabilityId, Account $mentorAccount, string $takenFrom, string $takenTo): bool
    {
        return DB::transaction(function () use ($sessionId, $availabilityId, $mentorAccount, $takenFrom, $takenTo) {
            $availability = Availability::findOrFail($availabilityId);
            $mentorMember = Member::where('cid', $mentorAccount->id)->firstOrFail();

            $session = Session::query()
                ->where('id', $sessionId)
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
                'taken_time' => now(),
            ]);

            DB::afterCommit(function () use ($session) {
                $this->notifyParticipants($session, 'accepted');
            });

            $this->createCoreBooking($session);

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
                'taken_time' => now(),
            ]);

            DB::afterCommit(function () use ($session, $previousDateTime) {
                $this->notifyParticipants($session, 'rescheduled', [
                    'previousDateTime' => $previousDateTime,
                ]);
            });

            $this->updateCoreBooking($session);

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
                'session_done' => 1,
            ]);

            CancelReason::create([
                'sesh_id' => $session->id,
                'sesh_type' => 'ME',
                'reason' => $reason,
                'reason_by' => $cancellerMember->id,
            ]);

            DB::afterCommit(function () use ($session, $reason, $cancellerAccount) {
                $this->notifyParticipants($session, 'cancelled', [
                    'reason' => $reason,
                    'cancellerAccount' => $cancellerAccount,
                ]);
            });

            $this->deleteCoreBooking($session);

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
            'taken_time' => now(),
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
                $cancellerAccount = $data['cancellerAccount'];

                if ($cancellerAccount->id === $studentAccount->id) {
                    $mentorAccount->notify(new MentoringSessionCancelledByStudentNotification($session, $data['reason']));
                    $studentAccount->notify(new MentoringSessionCancelledStudentConfirmationNotification($session));
                } else {
                    $studentAccount->notify(new MentoringSessionCancelledStudentNotification($session, $cancellerAccount, $data['reason']));
                    $mentorAccount->notify(new MentoringSessionCancelledMentorNotification($session, $data['reason']));
                }
                break;

            case 'reallocated':
                $oldMentorAccount = $data['oldMentorAccount'];
                $newMentorAccount = $data['newMentorAccount'];
                $reason = $data['reason'];
                $oldMentorName = $oldMentorAccount?->name ?? 'Unknown';
                $newMentorName = $newMentorAccount->name ?? 'Unknown';

                if ($studentAccount) {
                    $studentAccount->notify(new MentoringSessionReallocatedStudentNotification($session, $newMentorName));
                }

                if ($oldMentorAccount) {
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

    public function findOverlappingBookingForSession(Session $session): Session|ExamBooking|null
    {
        if (! $session->taken_date || ! $session->taken_from || ! $session->taken_to) {
            return null;
        }

        return $this->checkForOverlappingBookings(
            $session->position,
            $session->taken_date,
            $session->taken_from,
            $session->taken_to,
            $session->id
        );
    }

    public function overlapHeading(Session|ExamBooking $overlap): string
    {
        return $overlap instanceof Session ? 'Overlapping Session Detected' : 'Overlapping Exam Detected';
    }

    public function overlapDescription(Session|ExamBooking $overlap): string
    {
        $type = $overlap instanceof Session ? 'session' : 'exam';
        $from = Carbon::parse($overlap->taken_from)->format('H:i');
        $to = Carbon::parse($overlap->taken_to)->format('H:i');
        $staffName = $overlap instanceof Session ? $overlap->mentor?->name : $overlap->examiners?->primaryExaminer?->name;

        return "{$staffName} already has a {$type} booked on this position from {$from} to {$to}.";
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

    private function createCoreBooking(Session $session): void
    {
        $studentMember = Member::find($session->student_id);

        // Create the CTS booking first - it is the source of truth for the callsign,
        // since training positions may not exist in the core positions table.
        $ctsBooking = CtsBooking::create([
            'date' => $session->taken_date,
            'from' => $session->taken_from,
            'to' => $session->taken_to,
            'position' => $session->position,
            'member_id' => $session->student_id,
            'type' => 'ME',
        ]);

        Booking::create([
            'position_id' => Position::where('callsign', $session->position)->value('id'),
            'member_id' => $studentMember?->cid,
            'type' => Booking::TYPE_MENTORING,
            'starts_at' => Carbon::parse($session->taken_date)->format('Y-m-d').' '.$session->taken_from,
            'ends_at' => Carbon::parse($session->taken_date)->format('Y-m-d').' '.$session->taken_to,
            'bookable_type' => Session::class,
            'bookable_id' => $session->id,
            'cts_booking_id' => $ctsBooking->id,
        ]);
    }

    private function updateCoreBooking(Session $session): void
    {
        $booking = Booking::where('bookable_type', Session::class)
            ->where('bookable_id', $session->id)
            ->first();

        if (! $booking) {
            return;
        }

        $booking->update([
            'starts_at' => Carbon::parse($session->taken_date)->format('Y-m-d').' '.$session->taken_from,
            'ends_at' => Carbon::parse($session->taken_date)->format('Y-m-d').' '.$session->taken_to,
        ]);

        if ($booking->cts_booking_id) {
            CtsBooking::where('id', $booking->cts_booking_id)->update([
                'date' => $session->taken_date,
                'from' => $session->taken_from,
                'to' => $session->taken_to,
            ]);
        }
    }

    private function deleteCoreBooking(Session $session): void
    {
        $booking = Booking::where('bookable_type', Session::class)
            ->where('bookable_id', $session->id)
            ->first();

        if (! $booking) {
            return;
        }

        if ($booking->cts_booking_id) {
            CtsBooking::where('id', $booking->cts_booking_id)->delete();
        }

        $booking->delete();
    }
}
