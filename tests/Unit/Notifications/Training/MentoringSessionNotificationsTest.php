<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Training;

use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Notifications\Training\MentoringReportFiled;
use App\Notifications\Training\StudentMentoringNoShow;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MentoringSessionNotificationsTest extends TestCase
{
    use DatabaseTransactions;

    private Account $studentAccount;

    private Account $tgiAccount;

    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentAccount = Account::factory()->create([
            'name_first' => 'Alex',
            'name_last' => 'Student',
        ]);

        $studentMember = Member::factory()->create([
            'id' => $this->studentAccount->id,
            'cid' => $this->studentAccount->id,
            'name' => 'Alex Student',
        ]);

        $this->tgiAccount = Account::factory()->create([
            'name_first' => 'Taylor',
            'name_last' => 'Instructor',
        ]);

        $this->session = Session::factory()->accepted()->create([
            'student_id' => $studentMember->id,
            'position' => 'EGKK_TWR',
            'taken_date' => '2026-05-20',
            'taken_from' => '14:00:00',
            'taken_to' => '16:00:00',
        ]);

        $this->session->load('student');
    }

    #[Test]
    public function mentoring_report_filed_uses_expected_subject_view_and_content(): void
    {
        $notification = new MentoringReportFiled($this->session);
        $mail = $notification->toMail($this->studentAccount);

        $this->assertContains('mail', $notification->via($this->studentAccount));
        $this->assertSame('Session report finished', $mail->subject);
        $this->assertSame('emails.training.mentoring_report_filed', $mail->view);
        $this->assertSame(
            ['subject', 'recipient', 'session', 'reportUrl'],
            array_keys($mail->viewData),
        );
        $this->assertSame($this->session->id, $mail->viewData['session']->id);

        $html = View::make($mail->view, $mail->data())->render();

        $this->assertStringContainsString('Dear Alex Student', $html);
        $this->assertStringContainsString('your mentor has finished your report', $html);
        $this->assertStringContainsString('Position: EGKK_TWR', $html);
        $this->assertStringContainsString('Session date: 2026-05-20 14:00:00 - 16:00:00', $html);
        $this->assertStringContainsString('VATSIM UK Training Department', $html);
    }

    #[Test]
    public function student_mentoring_no_show_uses_expected_subject_view_and_content(): void
    {
        $notification = new StudentMentoringNoShow($this->session);
        $mail = $notification->toMail($this->tgiAccount);

        $this->assertContains('mail', $notification->via($this->tgiAccount));
        $this->assertSame('Student mentoring session no-show', $mail->subject);
        $this->assertSame('emails.training.student_mentoring_no_show', $mail->view);
        $this->assertSame(
            ['subject', 'recipient', 'session'],
            array_keys($mail->viewData),
        );
        $this->assertSame($this->session->id, $mail->viewData['session']->id);

        $html = View::make($mail->view, $mail->data())->render();

        $this->assertStringContainsString('Dear Taylor Instructor', $html);
        $this->assertStringContainsString('marked as a no-show', $html);
        $this->assertStringContainsString('Alex Student', $html);
        $this->assertStringContainsString('EGKK_TWR', $html);
        $this->assertStringContainsString('VATSIM UK Training Department', $html);
    }
}
