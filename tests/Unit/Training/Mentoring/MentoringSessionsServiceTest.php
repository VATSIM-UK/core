<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Mentoring;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Availability;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
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
use App\Services\Training\MentoringSessionsService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MentoringSessionsServiceTest extends TestCase
{
    use DatabaseTransactions;

    private MentoringSessionsService $service;

    private Account $mentorAccount;

    private Member $mentorMember;

    private Account $studentAccount;

    private Member $studentMember;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(MentoringSessionsService::class);

        $this->mentorAccount = Account::factory()->create();
        $this->mentorMember = Member::factory()->create([
            'id' => $this->mentorAccount->generateCTSInternalID($this->mentorAccount->id),
            'cid' => $this->mentorAccount->id,
        ]);

        $this->mentorAccount->givePermissionTo('training.beta');
        $this->mentorAccount->givePermissionTo('training.mentoring.view.*');

        $this->studentAccount = Account::factory()->create();
        $this->studentMember = Member::factory()->create([
            'id' => $this->studentAccount->generateCTSInternalID($this->studentAccount->id),
            'cid' => $this->studentAccount->id,
        ]);
    }

    #[Test]
    public function accept_session_throws_exception_when_availability_does_not_exist(): void
    {
        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => null,
        ]);

        $this->expectException(ModelNotFoundException::class);

        $this->service->acceptSession($session->id, 999999, $this->mentorAccount, '10:00', '12:00');
    }

    #[Test]
    public function accept_session_returns_false_when_student_has_no_pending_session(): void
    {
        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
        ]);

        $this->assertFalse($this->service->acceptSession(
            999999,
            $availability->id,
            $this->mentorAccount,
            '10:00',
            '12:00',
        ));
    }

    #[Test]
    public function accept_session_returns_false_when_pending_session_is_filed(): void
    {
        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => null,
            'filed' => now(),
        ]);

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
        ]);

        $this->assertFalse($this->service->acceptSession(
            $session->id,
            $availability->id,
            $this->mentorAccount,
            '10:00',
            '12:00',
        ));
    }

    #[Test]
    public function accept_session_returns_false_when_pending_session_is_cancelled(): void
    {
        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => null,
            'cancelled_datetime' => now(),
        ]);

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
        ]);

        $this->assertFalse($this->service->acceptSession(
            $session->id,
            $availability->id,
            $this->mentorAccount,
            '10:00',
            '12:00',
        ));
    }

    #[Test]
    public function accept_session_returns_false_when_session_id_does_not_belong_to_the_availabilitys_student(): void
    {
        $otherStudentAccount = Account::factory()->create();
        $otherStudentMember = Member::factory()->create([
            'id' => $otherStudentAccount->generateCTSInternalID($otherStudentAccount->id),
            'cid' => $otherStudentAccount->id,
        ]);

        $mismatchedSession = Session::factory()->create([
            'student_id' => $otherStudentMember->id,
            'mentor_id' => null,
        ]);

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
        ]);

        $this->assertFalse($this->service->acceptSession(
            $mismatchedSession->id,
            $availability->id,
            $this->mentorAccount,
            '10:00',
            '12:00',
        ));

        $this->assertNull($mismatchedSession->fresh()->mentor_id);
    }

    #[Test]
    public function accept_session_assigns_mentor_and_scheduling_fields(): void
    {
        Notification::fake();

        $position = Position::factory()->create(['callsign' => 'EGLL_APP']);

        $pendingSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'mentor_id' => null,
            'taken' => 0,
        ]);

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $this->assertTrue($this->service->acceptSession(
            $pendingSession->id,
            $availability->id,
            $this->mentorAccount,
            '10:00',
            '12:00',
        ));

        $pendingSession->refresh();

        $this->assertSame($this->mentorMember->id, $pendingSession->mentor_id);
        $this->assertSame(1, $pendingSession->taken);
        $this->assertSame(Carbon::tomorrow()->format('Y-m-d'), Carbon::parse($pendingSession->taken_date)->format('Y-m-d'));
        $this->assertSame('10:00:00', Carbon::parse($pendingSession->taken_from)->format('H:i:s'));
        $this->assertSame('12:00:00', Carbon::parse($pendingSession->taken_to)->format('H:i:s'));
        $this->assertNotNull($pendingSession->taken_time);

        $this->assertDatabaseHas('bookings', [
            'position_id' => $position->id,
            'member_id' => $this->studentAccount->id,
            'type' => Booking::TYPE_MENTORING,
            'bookable_type' => Session::class,
            'bookable_id' => $pendingSession->id,
        ]);
    }

    #[Test]
    public function accept_session_only_assigns_the_specified_session_when_multiple_pending_sessions_exist(): void
    {
        Notification::fake();

        $firstPending = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'mentor_id' => null,
        ]);

        $secondPending = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGKK_APP',
            'mentor_id' => null,
        ]);

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
            'from' => '09:00:00',
            'to' => '13:00:00',
        ]);

        $this->assertTrue($this->service->acceptSession(
            $secondPending->id,
            $availability->id,
            $this->mentorAccount,
            '10:00',
            '12:00',
        ));

        $firstPending->refresh();
        $secondPending->refresh();

        $this->assertNull($firstPending->mentor_id);
        $this->assertSame($this->mentorMember->id, $secondPending->mentor_id);
    }

    #[Test]
    public function accept_session_leaves_unspecified_pending_session_bookable_by_a_second_call(): void
    {
        Notification::fake();

        $firstPending = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'mentor_id' => null,
        ]);

        $secondPending = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGKK_APP',
            'mentor_id' => null,
        ]);

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
            'from' => '09:00:00',
            'to' => '13:00:00',
        ]);

        $this->assertTrue($this->service->acceptSession(
            $secondPending->id,
            $availability->id,
            $this->mentorAccount,
            '10:00',
            '11:00',
        ));

        $this->assertTrue($this->service->acceptSession(
            $firstPending->id,
            $availability->id,
            $this->mentorAccount,
            '11:00',
            '12:00',
        ));

        $this->assertSame($this->mentorMember->id, $firstPending->fresh()->mentor_id);
        $this->assertSame($this->mentorMember->id, $secondPending->fresh()->mentor_id);
    }

    #[Test]
    public function accept_session_throws_exception_when_mentor_is_not_authorized_for_the_position(): void
    {
        $unauthorizedMentorAccount = Account::factory()->create();
        Member::factory()->create([
            'id' => $unauthorizedMentorAccount->generateCTSInternalID($unauthorizedMentorAccount->id),
            'cid' => $unauthorizedMentorAccount->id,
        ]);

        $pendingSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'mentor_id' => null,
        ]);

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
            'from' => '09:00:00',
            'to' => '13:00:00',
        ]);

        $this->expectException(AuthorizationException::class);

        $this->service->acceptSession(
            $pendingSession->id,
            $availability->id,
            $unauthorizedMentorAccount,
            '10:00',
            '12:00',
        );
    }

    #[Test]
    public function accept_session_throws_exception_when_requested_times_fall_outside_availability_window(): void
    {
        $pendingSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'mentor_id' => null,
        ]);

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $this->expectException(InvalidArgumentException::class);

        $this->service->acceptSession(
            $pendingSession->id,
            $availability->id,
            $this->mentorAccount,
            '09:00',
            '13:00',
        );
    }

    #[Test]
    public function accept_session_throws_exception_when_end_time_is_not_after_start_time(): void
    {
        $pendingSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'mentor_id' => null,
        ]);

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
            'from' => '09:00:00',
            'to' => '13:00:00',
        ]);

        $this->expectException(InvalidArgumentException::class);

        $this->service->acceptSession(
            $pendingSession->id,
            $availability->id,
            $this->mentorAccount,
            '12:00',
            '10:00',
        );
    }

    #[Test]
    public function accept_session_sends_notifications_to_student_and_mentor(): void
    {
        Notification::fake();

        $pendingSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'mentor_id' => null,
        ]);

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
            'from' => '09:00:00',
            'to' => '13:00:00',
        ]);

        $this->assertTrue($this->service->acceptSession(
            $pendingSession->id,
            $availability->id,
            $this->mentorAccount,
            '10:00',
            '12:00',
        ));

        Notification::assertSentTo($this->studentAccount, MentoringSessionAcceptedStudentNotification::class);
        Notification::assertSentTo($this->mentorAccount, MentoringSessionAcceptedMentorNotification::class);
    }

    #[Test]
    public function reschedule_session_throws_exception_when_session_does_not_exist(): void
    {
        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
        ]);

        $this->expectException(ModelNotFoundException::class);

        $this->service->rescheduleSession(999999, $availability->id, '14:00', '16:00', $this->mentorAccount);
    }

    #[Test]
    public function reschedule_session_throws_exception_when_availability_does_not_exist(): void
    {
        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'taken' => 1,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        $this->expectException(ModelNotFoundException::class);

        $this->service->rescheduleSession($session->id, 999999, '14:00', '16:00', $this->mentorAccount);
    }

    #[Test]
    public function reschedule_session_updates_scheduling_fields_from_availability(): void
    {
        Notification::fake();

        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'taken_date' => '2026-05-20',
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        Booking::create([
            'type' => Booking::TYPE_MENTORING,
            'starts_at' => '2026-05-20 10:00:00',
            'ends_at' => '2026-05-20 12:00:00',
            'bookable_type' => Session::class,
            'bookable_id' => $session->id,
        ]);

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
            'from' => '14:00:00',
            'to' => '16:00:00',
        ]);

        $this->assertTrue($this->service->rescheduleSession(
            $session->id,
            $availability->id,
            '14:00',
            '16:00',
            $this->mentorAccount
        ));

        $session->refresh();

        $this->assertSame(Carbon::tomorrow()->format('Y-m-d'), Carbon::parse($session->taken_date)->format('Y-m-d'));
        $this->assertSame('14:00:00', Carbon::parse($session->taken_from)->format('H:i:s'));
        $this->assertSame('16:00:00', Carbon::parse($session->taken_to)->format('H:i:s'));
        $this->assertSame($this->mentorMember->id, $session->mentor_id);
        $this->assertSame('EGLL_APP', $session->position);
        $this->assertNotNull($session->taken_time);

        $this->assertDatabaseHas('bookings', [
            'bookable_type' => Session::class,
            'bookable_id' => $session->id,
        ]);

        $booking = Booking::where('bookable_type', Session::class)
            ->where('bookable_id', $session->id)
            ->first();

        $this->assertNotNull($booking);
        $this->assertEquals(Carbon::tomorrow()->format('Y-m-d'), $booking->starts_at->format('Y-m-d'));
        $this->assertEquals('14:00', $booking->starts_at->format('H:i'));
        $this->assertEquals('16:00', $booking->ends_at->format('H:i'));
    }

    #[Test]
    public function reschedule_session_sends_notifications_to_student_and_mentor(): void
    {
        Notification::fake();

        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'taken' => 1,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow()->addDay(),
            'from' => '12:00',
            'to' => '20:00',
        ]);

        $this->assertTrue($this->service->rescheduleSession(
            $session->id,
            $availability->id,
            '14:00',
            '16:00',
            $this->mentorAccount
        ));

        Notification::assertSentTo($this->studentAccount, MentoringSessionRescheduledStudentNotification::class);
        Notification::assertSentTo($this->mentorAccount, MentoringSessionRescheduledMentorNotification::class);
    }

    #[Test]
    public function cancel_session_throws_exception_when_session_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->cancelSession(
            999999,
            'Unable to conduct session on this date.',
            $this->mentorAccount,
        );
    }

    #[Test]
    public function cancel_session_marks_session_as_cancelled(): void
    {
        Notification::fake();

        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'taken' => 1,
            'cancelled_datetime' => null,
        ]);

        $reason = 'Unable to conduct session on this date.';

        $this->assertTrue($this->service->cancelSession($session->id, $reason, $this->mentorAccount));

        $this->assertNotNull($session->fresh()->cancelled_datetime);

        $this->assertDatabaseMissing('bookings', [
            'bookable_type' => Session::class,
            'bookable_id' => $session->id,
        ]);
    }

    #[Test]
    public function cancel_session_inserts_mentoring_cancel_reason_record(): void
    {
        Notification::fake();

        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'taken' => 1,
        ]);

        $reason = 'Mentor is unavailable due to a prior commitment.';

        $this->service->cancelSession($session->id, $reason, $this->mentorAccount);

        $this->assertDatabaseHas('cancel_reason', [
            'sesh_id' => $session->id,
            'sesh_type' => 'ME',
            'reason' => $reason,
            'reason_by' => $this->mentorMember->id,
        ], 'cts');
    }

    #[Test]
    public function cancel_session_creates_new_pending_session_request_for_student(): void
    {
        Notification::fake();

        $session = Session::factory()->create([
            'rts_id' => 42,
            'position' => 'EGLL_APP',
            'progress_sheet_id' => 7,
            'student_id' => $this->studentMember->id,
            'student_rating' => 3,
            'mentor_id' => $this->mentorMember->id,
            'taken' => 1,
        ]);

        $pendingBefore = Session::query()
            ->where('student_id', $this->studentMember->id)
            ->whereNull('mentor_id')
            ->whereNull('cancelled_datetime')
            ->count();

        $this->service->cancelSession(
            $session->id,
            'Unable to conduct session on this date.',
            $this->mentorAccount,
        );

        $newPending = Session::query()
            ->where('student_id', $this->studentMember->id)
            ->whereNull('mentor_id')
            ->whereNull('cancelled_datetime')
            ->where('id', '!=', $session->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($newPending);
        $this->assertSame($pendingBefore + 1, Session::query()
            ->where('student_id', $this->studentMember->id)
            ->whereNull('mentor_id')
            ->whereNull('cancelled_datetime')
            ->count());
        $this->assertSame(42, $newPending->rts_id);
        $this->assertSame('EGLL_APP', $newPending->position);
        $this->assertSame(7, $newPending->progress_sheet_id);
        $this->assertSame(3, $newPending->student_rating);
        $this->assertNotNull($newPending->request_time);
    }

    #[Test]
    public function cancel_session_sends_notifications_to_student_and_mentor(): void
    {
        Notification::fake();

        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'taken' => 1,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        $this->assertTrue($this->service->cancelSession(
            $session->id,
            'Unable to conduct session on this date.',
            $this->mentorAccount,
        ));

        Notification::assertSentTo($this->studentAccount, MentoringSessionCancelledStudentNotification::class);
        Notification::assertSentTo($this->mentorAccount, MentoringSessionCancelledMentorNotification::class);
    }

    #[Test]
    public function cancel_session_by_student_marks_session_as_cancelled(): void
    {
        Notification::fake();

        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'taken' => 1,
            'cancelled_datetime' => null,
        ]);

        $this->assertTrue($this->service->cancelSession($session->id, 'Unable to attend.', $this->studentAccount));

        $this->assertNotNull($session->fresh()->cancelled_datetime);
    }

    #[Test]
    public function cancel_session_by_student_inserts_cancel_reason_record_with_student_id(): void
    {
        Notification::fake();

        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'taken' => 1,
        ]);

        $reason = 'Student has a prior commitment.';

        $this->service->cancelSession($session->id, $reason, $this->studentAccount);

        $this->assertDatabaseHas('cancel_reason', [
            'sesh_id' => $session->id,
            'sesh_type' => 'ME',
            'reason' => $reason,
            'reason_by' => $this->studentMember->id,
        ], 'cts');
    }

    #[Test]
    public function cancel_session_by_student_creates_new_pending_session_request(): void
    {
        Notification::fake();

        $session = Session::factory()->create([
            'rts_id' => 42,
            'position' => 'EGLL_APP',
            'progress_sheet_id' => 7,
            'student_id' => $this->studentMember->id,
            'student_rating' => 3,
            'mentor_id' => $this->mentorMember->id,
            'taken' => 1,
        ]);

        $this->service->cancelSession(
            $session->id,
            'Unable to attend.',
            $this->studentAccount,
        );

        $newPending = Session::query()
            ->where('student_id', $this->studentMember->id)
            ->whereNull('mentor_id')
            ->whereNull('cancelled_datetime')
            ->where('id', '!=', $session->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($newPending);
        $this->assertSame(42, $newPending->rts_id);
        $this->assertSame('EGLL_APP', $newPending->position);
        $this->assertSame(7, $newPending->progress_sheet_id);
        $this->assertSame(3, $newPending->student_rating);
        $this->assertNotNull($newPending->request_time);
    }

    #[Test]
    public function cancel_session_by_student_sends_correct_notifications(): void
    {
        Notification::fake();

        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'taken' => 1,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        $this->assertTrue($this->service->cancelSession(
            $session->id,
            'Unable to attend.',
            $this->studentAccount,
        ));

        Notification::assertSentTo($this->mentorAccount, MentoringSessionCancelledByStudentNotification::class);
        Notification::assertSentTo($this->studentAccount, MentoringSessionCancelledStudentConfirmationNotification::class);
        Notification::assertNotSentTo($this->studentAccount, MentoringSessionCancelledStudentNotification::class);
        Notification::assertNotSentTo($this->mentorAccount, MentoringSessionCancelledMentorNotification::class);
    }

    #[Test]
    public function reallocate_session_updates_mentor_id(): void
    {
        Notification::fake();

        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        $newMentorAccount = Account::factory()->create();
        $newMentorMember = Member::factory()->create([
            'id' => $newMentorAccount->generateCTSInternalID($newMentorAccount->id),
            'cid' => $newMentorAccount->id,
        ]);

        $trainingPosition = TrainingPosition::factory()->create([
            'category' => 'S3 Training',
            'cts_positions' => ['EGLL_APP'],
        ]);

        MentorTrainingPosition::create([
            'account_id' => $newMentorAccount->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $trainingPosition->id,
            'created_by' => $newMentorAccount->id,
        ]);

        $this->assertTrue($this->service->reallocateSession(
            $session->id,
            $newMentorAccount->id,
            $this->mentorAccount,
            'Mentor is unavailable due to prior commitments.',
        ));

        $session->refresh();

        $this->assertSame($newMentorMember->id, $session->mentor_id);
        $this->assertSame('10:00:00', Carbon::parse($session->taken_from)->format('H:i:s'));
        $this->assertSame('12:00:00', Carbon::parse($session->taken_to)->format('H:i:s'));
        $this->assertNotNull($session->taken_time);
    }

    #[Test]
    public function reallocate_session_sends_notifications_to_all_parties(): void
    {
        Notification::fake();

        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        $newMentorAccount = Account::factory()->create();
        Member::factory()->create([
            'id' => $newMentorAccount->generateCTSInternalID($newMentorAccount->id),
            'cid' => $newMentorAccount->id,
        ]);

        $trainingPosition = TrainingPosition::factory()->create([
            'category' => 'S3 Training',
            'cts_positions' => ['EGLL_APP'],
        ]);

        MentorTrainingPosition::create([
            'account_id' => $newMentorAccount->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $trainingPosition->id,
            'created_by' => $newMentorAccount->id,
        ]);

        $reason = 'Mentor is unavailable due to prior commitments.';

        $this->assertTrue($this->service->reallocateSession(
            $session->id,
            $newMentorAccount->id,
            $this->mentorAccount,
            $reason,
        ));

        Notification::assertSentTo($this->studentAccount, MentoringSessionReallocatedStudentNotification::class);
        Notification::assertSentTo($this->mentorAccount, MentoringSessionReallocatedOldMentorNotification::class);
        Notification::assertSentTo($newMentorAccount, MentoringSessionReallocatedNewMentorNotification::class);
    }

    #[Test]
    public function check_for_overlapping_bookings_returns_null_when_no_overlap(): void
    {
        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'mentor_id' => $this->mentorMember->id,
            'taken' => 1,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        $result = $this->service->checkForOverlappingBookings(
            'EGLL_APP',
            Carbon::tomorrow()->format('Y-m-d'),
            '14:00',
            '16:00',
        );

        $this->assertNull($result);
    }

    #[Test]
    public function check_for_overlapping_bookings_returns_session_when_overlap_exists(): void
    {
        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'mentor_id' => $this->mentorMember->id,
            'taken' => 1,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
            'cancelled_datetime' => null,
        ]);

        $result = $this->service->checkForOverlappingBookings(
            'EGLL_APP',
            Carbon::tomorrow()->format('Y-m-d'),
            '11:00',
            '13:00',
        );

        $this->assertInstanceOf(Session::class, $result);
        $this->assertSame('10:00:00', $result->taken_from);
        $this->assertSame('12:00:00', $result->taken_to);
    }

    #[Test]
    public function check_for_overlapping_bookings_returns_exam_when_overlap_exists(): void
    {
        ExamBooking::factory()->create([
            'position_1' => 'EGLL_APP',
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        $result = $this->service->checkForOverlappingBookings(
            'EGLL_APP',
            Carbon::tomorrow()->format('Y-m-d'),
            '11:00',
            '13:00',
        );

        $this->assertInstanceOf(ExamBooking::class, $result);
        $this->assertSame('10:00:00', $result->taken_from);
        $this->assertSame('12:00:00', $result->taken_to);
    }

    #[Test]
    public function check_for_overlapping_bookings_ignores_session_when_ignore_id_is_provided(): void
    {
        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'mentor_id' => $this->mentorMember->id,
            'taken' => 1,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
            'cancelled_datetime' => null,
        ]);

        $result = $this->service->checkForOverlappingBookings(
            'EGLL_APP',
            Carbon::tomorrow()->format('Y-m-d'),
            '11:00',
            '13:00',
            $session->id,
        );

        $this->assertNull($result);
    }

    #[Test]
    public function check_for_overlapping_bookings_returns_session_when_both_session_and_exam_overlap(): void
    {
        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'mentor_id' => $this->mentorMember->id,
            'taken' => 1,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
            'cancelled_datetime' => null,
        ]);

        ExamBooking::factory()->create([
            'position_1' => 'EGLL_APP',
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        $result = $this->service->checkForOverlappingBookings(
            'EGLL_APP',
            Carbon::tomorrow()->format('Y-m-d'),
            '11:00',
            '13:00',
        );

        $this->assertInstanceOf(Session::class, $result);
    }
}
