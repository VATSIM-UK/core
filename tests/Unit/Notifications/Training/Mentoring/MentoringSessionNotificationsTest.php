<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Training\Mentoring;

use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Notifications\Training\Mentoring\MentoringSessionAcceptedMentorNotification;
use App\Notifications\Training\Mentoring\MentoringSessionAcceptedStudentNotification;
use App\Notifications\Training\Mentoring\MentoringSessionCancelledMentorNotification;
use App\Notifications\Training\Mentoring\MentoringSessionCancelledStudentNotification;
use App\Notifications\Training\Mentoring\MentoringSessionRescheduledMentorNotification;
use App\Notifications\Training\Mentoring\MentoringSessionRescheduledStudentNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MentoringSessionNotificationsTest extends TestCase
{
    use DatabaseTransactions;

    private Account $studentAccount;

    private Account $mentorAccount;

    private Member $studentMember;

    private Member $mentorMember;

    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentAccount = Account::factory()->create([
            'name_first' => 'Alex',
            'name_last' => 'Student',
        ]);

        $this->studentMember = Member::factory()->create([
            'id' => Account::generateCTSInternalID(),
            'cid' => $this->studentAccount->id,
        ]);

        $this->mentorAccount = Account::factory()->create([
            'name_first' => 'Jamie',
            'name_last' => 'Mentor',
        ]);

        $this->mentorMember = Member::factory()->create([
            'id' => Account::generateCTSInternalID(),
            'cid' => $this->mentorAccount->id,
        ]);

        $this->session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'taken_date' => '2026-05-20',
            'taken_from' => '14:00:00',
            'taken_to' => '16:00:00',
        ]);
    }

    #[Test]
    public function accepted_student_notification_uses_expected_subject_view_and_data(): void
    {
        $notification = new MentoringSessionAcceptedStudentNotification($this->session);
        $mail = $notification->toMail($this->studentAccount);

        $this->assertContains('mail', $notification->via($this->studentAccount));
        $this->assertSame('Your Mentoring Session has been accepted', $mail->subject);
        $this->assertSame('emails.training.mentoring.session_accepted_student', $mail->view);
        $this->assertSame(
            ['recipient', 'session', 'position', 'sessionDateTime', 'mentorName'],
            array_keys($mail->viewData),
        );
        $this->assertSame($this->session->id, $mail->viewData['session']->id);
        $this->assertSame('EGLL_APP', $mail->viewData['position']);

        $html = View::make($mail->view, $mail->data())->render();

        $this->assertStringContainsString('Dear Alex Student', $html);
        $this->assertStringContainsString('Your mentoring session has been scheduled', $html);
        $this->assertStringContainsString('EGLL_APP', $html);
        $this->assertStringContainsString('Jamie Mentor', $html);
        $this->assertStringContainsString($this->session->formattedSessionDateTime(), $html);
    }

    #[Test]
    public function accepted_mentor_notification_uses_expected_subject_view_and_data(): void
    {
        $notification = new MentoringSessionAcceptedMentorNotification($this->session);
        $mail = $notification->toMail($this->mentorAccount);

        $this->assertContains('mail', $notification->via($this->mentorAccount));
        $this->assertSame('Mentoring Session Accepted', $mail->subject);
        $this->assertSame('emails.training.mentoring.session_accepted_mentor', $mail->view);
        $this->assertSame(
            ['recipient', 'session', 'position', 'sessionDateTime', 'studentName', 'studentCid'],
            array_keys($mail->viewData),
        );
        $this->assertSame($this->session->id, $mail->viewData['session']->id);
        $this->assertSame((string) $this->studentAccount->id, (string) $mail->viewData['studentCid']);

        $html = View::make($mail->view, $mail->data())->render();

        $this->assertStringContainsString('Dear Jamie Mentor', $html);
        $this->assertStringContainsString('Alex Student', $html);
        $this->assertStringContainsString('EGLL_APP', $html);
        $this->assertStringContainsString((string) $this->studentAccount->id, $html);
    }

    #[Test]
    public function cancelled_student_notification_uses_expected_subject_view_and_data(): void
    {
        $reason = 'Mentor is unavailable on this date due to prior commitments.';

        $notification = new MentoringSessionCancelledStudentNotification(
            $this->session,
            $this->mentorAccount,
            $reason,
        );
        $mail = $notification->toMail($this->studentAccount);

        $this->assertContains('mail', $notification->via($this->studentAccount));
        $this->assertSame('Your Mentoring Session has been Cancelled', $mail->subject);
        $this->assertSame('emails.training.mentoring.session_cancelled_student', $mail->view);
        $this->assertSame(
            ['recipient', 'session', 'cancelledByMentor', 'reason'],
            array_keys($mail->viewData),
        );
        $this->assertSame($this->mentorAccount->id, $mail->viewData['cancelledByMentor']->id);
        $this->assertSame($reason, $mail->viewData['reason']);

        $html = View::make($mail->view, $mail->data())->render();

        $this->assertStringContainsString('Dear Alex Student', $html);
        $this->assertStringContainsString('Jamie Mentor', $html);
        $this->assertStringContainsString((string) $this->mentorAccount->id, $html);
        $this->assertStringContainsString('has cancelled your mentoring session', $html);
        $this->assertStringContainsString('EGLL_APP', $html);
        $this->assertStringContainsString($reason, $html);
        $this->assertStringContainsString('remain in the system', $html);
    }

    #[Test]
    public function cancelled_mentor_notification_uses_expected_subject_view_and_data(): void
    {
        $reason = 'Student requested to postpone and I cannot accommodate a new time.';

        $notification = new MentoringSessionCancelledMentorNotification($this->session, $reason);
        $mail = $notification->toMail($this->mentorAccount);

        $this->assertContains('mail', $notification->via($this->mentorAccount));
        $this->assertSame('Mentoring Session Cancelled', $mail->subject);
        $this->assertSame('emails.training.mentoring.session_cancelled_mentor', $mail->view);
        $this->assertSame(
            ['recipient', 'session', 'reason'],
            array_keys($mail->viewData),
        );
        $this->assertSame($reason, $mail->viewData['reason']);

        $html = View::make($mail->view, $mail->data())->render();

        $this->assertStringContainsString('Dear Jamie Mentor', $html);
        $this->assertStringContainsString('You have cancelled your mentoring session', $html);
        $this->assertStringContainsString('Alex Student', $html);
        $this->assertStringContainsString('EGLL_APP', $html);
        $this->assertStringContainsString($reason, $html);
    }

    #[Test]
    public function rescheduled_student_notification_uses_expected_subject_view_and_data(): void
    {
        $previousDateTime = 'Monday 19th May 2026, 10:00 - 12:00';

        $notification = new MentoringSessionRescheduledStudentNotification($this->session, $previousDateTime);
        $mail = $notification->toMail($this->studentAccount);

        $this->assertContains('mail', $notification->via($this->studentAccount));
        $this->assertSame('Your Mentoring Session has been Rescheduled', $mail->subject);
        $this->assertSame('emails.training.mentoring.session_rescheduled_student', $mail->view);
        $this->assertSame(
            ['recipient', 'session', 'position', 'previousDateTime', 'sessionDateTime', 'mentorName'],
            array_keys($mail->viewData),
        );
        $this->assertSame($previousDateTime, $mail->viewData['previousDateTime']);
        $this->assertSame($this->session->formattedSessionDateTime(), $mail->viewData['sessionDateTime']);

        $html = View::make($mail->view, $mail->data())->render();

        $this->assertStringContainsString('Dear Alex Student', $html);
        $this->assertStringContainsString('has been rescheduled by Jamie Mentor', $html);
        $this->assertStringContainsString($previousDateTime, $html);
        $this->assertStringContainsString('EGLL_APP', $html);
        $this->assertStringContainsString($this->session->formattedSessionDateTime(), $html);
    }

    #[Test]
    public function rescheduled_mentor_notification_uses_expected_subject_view_and_data(): void
    {
        $previousDateTime = 'Monday 19th May 2026, 10:00 - 12:00';

        $notification = new MentoringSessionRescheduledMentorNotification($this->session, $previousDateTime);
        $mail = $notification->toMail($this->mentorAccount);

        $this->assertContains('mail', $notification->via($this->mentorAccount));
        $this->assertSame('Mentoring Session Rescheduled', $mail->subject);
        $this->assertSame('emails.training.mentoring.session_rescheduled_mentor', $mail->view);
        $this->assertSame(
            ['recipient', 'session', 'position', 'previousDateTime', 'sessionDateTime', 'studentName', 'studentCid'],
            array_keys($mail->viewData),
        );
        $this->assertSame($previousDateTime, $mail->viewData['previousDateTime']);

        $html = View::make($mail->view, $mail->data())->render();

        $this->assertStringContainsString('Dear Jamie Mentor', $html);
        $this->assertStringContainsString('You have rescheduled your mentoring session', $html);
        $this->assertStringContainsString('Alex Student', $html);
        $this->assertStringContainsString((string) $this->studentAccount->id, $html);
        $this->assertStringContainsString($previousDateTime, $html);
    }
}
