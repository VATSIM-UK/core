<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Mentoring;

use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Notifications\Training\Mentoring\MentoringSessionAcceptedMentorNotification;
use App\Notifications\Training\Mentoring\MentoringSessionAcceptedStudentNotification;
use App\Notifications\Training\Mentoring\MentoringSessionCancelledMentorNotification;
use App\Notifications\Training\Mentoring\MentoringSessionCancelledStudentNotification;
use App\Notifications\Training\Mentoring\MentoringSessionRescheduledMentorNotification;
use App\Notifications\Training\Mentoring\MentoringSessionRescheduledStudentNotification;
use App\Services\Training\MentoringSessionsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
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

        $this->studentAccount = Account::factory()->create();
        $this->studentMember = Member::factory()->create([
            'id' => $this->studentAccount->generateCTSInternalID($this->studentAccount->id),
            'cid' => $this->studentAccount->id,
        ]);
    }

    #[Test]
    public function accept_session_returns_false_when_availability_does_not_exist(): void
    {
        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => null,
        ]);

        $this->assertFalse($this->service->acceptSession(999999, $this->mentorAccount->id, '10:00', '12:00'));
    }

    #[Test]
    public function accept_session_returns_false_when_student_has_no_pending_session(): void
    {
        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
        ]);

        $this->assertFalse($this->service->acceptSession(
            $availability->id,
            $this->mentorAccount->id,
            '10:00',
            '12:00',
        ));
    }

    #[Test]
    public function accept_session_returns_false_when_pending_session_is_filed(): void
    {
        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => null,
            'filed' => now(),
        ]);

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
        ]);

        $this->assertFalse($this->service->acceptSession(
            $availability->id,
            $this->mentorAccount->id,
            '10:00',
            '12:00',
        ));
    }

    #[Test]
    public function accept_session_returns_false_when_pending_session_is_cancelled(): void
    {
        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => null,
            'cancelled_datetime' => now(),
        ]);

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
        ]);

        $this->assertFalse($this->service->acceptSession(
            $availability->id,
            $this->mentorAccount->id,
            '10:00',
            '12:00',
        ));
    }

    #[Test]
    public function accept_session_assigns_mentor_and_scheduling_fields(): void
    {
        Notification::fake();

        $pendingSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'mentor_id' => null,
            'taken' => 0,
        ]);

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::parse('2026-06-15'),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $this->assertTrue($this->service->acceptSession(
            $availability->id,
            $this->mentorAccount->id,
            '10:00',
            '12:00',
        ));

        $pendingSession->refresh();

        $this->assertSame($this->mentorAccount->id, $pendingSession->mentor_id);
        $this->assertSame(1, $pendingSession->taken);
        $this->assertSame('2026-06-15', Carbon::parse($pendingSession->taken_date)->format('Y-m-d'));
        $this->assertSame('10:00:00', Carbon::parse($pendingSession->taken_from)->format('H:i:s'));
        $this->assertSame('12:00:00', Carbon::parse($pendingSession->taken_to)->format('H:i:s'));
    }

    #[Test]
    public function accept_session_assigns_first_pending_session_when_multiple_exist(): void
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
        ]);

        $this->assertTrue($this->service->acceptSession(
            $availability->id,
            $this->mentorAccount->id,
            '10:00',
            '12:00',
        ));

        $firstPending->refresh();
        $secondPending->refresh();

        $assignedCount = collect([$firstPending, $secondPending])
            ->filter(fn (Session $session) => $session->mentor_id !== null)
            ->count();

        $this->assertSame(1, $assignedCount);
    }

    #[Test]
    public function accept_session_sends_notifications_to_student_and_mentor(): void
    {
        Notification::fake();

        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'position' => 'EGLL_APP',
            'mentor_id' => null,
        ]);

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
        ]);

        $this->assertTrue($this->service->acceptSession(
            $availability->id,
            $this->mentorAccount->id,
            '10:00',
            '12:00',
        ));

        Notification::assertSentTo($this->studentAccount, MentoringSessionAcceptedStudentNotification::class);
        Notification::assertSentTo($this->mentorAccount, MentoringSessionAcceptedMentorNotification::class);
    }

    #[Test]
    public function reschedule_session_returns_false_when_session_does_not_exist(): void
    {
        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow(),
        ]);

        $this->assertFalse($this->service->rescheduleSession(999999, $availability->id, '14:00', '16:00'));
    }

    #[Test]
    public function reschedule_session_returns_false_when_availability_does_not_exist(): void
    {
        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'taken' => 1,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        $this->assertFalse($this->service->rescheduleSession($session->id, 999999, '14:00', '16:00'));
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

        $availability = Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::parse('2026-06-20'),
            'from' => '14:00:00',
            'to' => '16:00:00',
        ]);

        $this->assertTrue($this->service->rescheduleSession(
            $session->id,
            $availability->id,
            '14:00',
            '16:00',
        ));

        $session->refresh();

        $this->assertSame('2026-06-20', Carbon::parse($session->taken_date)->format('Y-m-d'));
        $this->assertSame('14:00:00', Carbon::parse($session->taken_from)->format('H:i:s'));
        $this->assertSame('16:00:00', Carbon::parse($session->taken_to)->format('H:i:s'));
        $this->assertSame($this->mentorMember->id, $session->mentor_id);
        $this->assertSame('EGLL_APP', $session->position);
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
        ]);

        $this->assertTrue($this->service->rescheduleSession(
            $session->id,
            $availability->id,
            '14:00',
            '16:00',
        ));

        Notification::assertSentTo($this->studentAccount, MentoringSessionRescheduledStudentNotification::class);
        Notification::assertSentTo($this->mentorAccount, MentoringSessionRescheduledMentorNotification::class);
    }

    #[Test]
    public function cancel_session_returns_false_when_session_does_not_exist(): void
    {
        $this->assertFalse($this->service->cancelSession(
            999999,
            'Unable to conduct session on this date.',
            $this->mentorAccount->id,
        ));
    }

    #[Test]
    public function cancel_session_throws_when_canceller_account_does_not_exist(): void
    {
        $session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'taken' => 1,
        ]);

        $this->expectException(ModelNotFoundException::class);

        $this->service->cancelSession($session->id, 'Reason here.', 999999);
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

        $this->assertTrue($this->service->cancelSession($session->id, $reason, $this->mentorAccount->id));

        $this->assertNotNull($session->fresh()->cancelled_datetime);
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

        $this->service->cancelSession($session->id, $reason, $this->mentorAccount->id);

        $this->assertDatabaseHas('cancel_reason', [
            'sesh_id' => $session->id,
            'sesh_type' => 'ME',
            'reason' => $reason,
            'reason_by' => $this->mentorAccount->id,
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
            $this->mentorAccount->id,
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
            $this->mentorAccount->id,
        ));

        Notification::assertSentTo($this->studentAccount, MentoringSessionCancelledStudentNotification::class);
        Notification::assertSentTo($this->mentorAccount, MentoringSessionCancelledMentorNotification::class);
    }
}
