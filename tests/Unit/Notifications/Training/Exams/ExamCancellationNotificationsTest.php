<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Training\Exams;

use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Notifications\Training\Exams\ExamCancelledByExaminerStudentNotification;
use App\Notifications\Training\Exams\ExamCancelledExaminerNotification;
use App\Notifications\Training\Exams\ExamCancelledStudentNotification;
use App\Notifications\Training\Exams\ExamSessionCancelledForCoExaminerNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExamCancellationNotificationsTest extends TestCase
{
    use DatabaseTransactions;

    private Account $studentAccount;

    private Account $examinerAccount;

    private Member $studentMember;

    private ExamBooking $examBooking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentAccount = Account::factory()->create([
            'name_first' => 'Alex',
            'name_last' => 'Student',
        ]);
        $this->studentMember = Member::factory()->create([
            'id' => $this->studentAccount->id,
            'cid' => $this->studentAccount->id,
        ]);

        $this->examinerAccount = Account::factory()->create([
            'name_first' => 'Jamie',
            'name_last' => 'Examiner',
        ]);

        $this->examBooking = ExamBooking::factory()->create([
            'student_id' => $this->studentMember->id,
            'exam' => 'TWR',
            'position_1' => 'EGKK_TWR',
            'taken' => 1,
            'taken_date' => '2026-05-20',
            'taken_from' => '14:00:00',
            'taken_to' => '16:00:00',
        ]);
    }

    #[Test]
    public function student_self_cancel_notification_uses_expected_subject_view_and_data(): void
    {
        $notification = new ExamCancelledStudentNotification($this->examBooking);
        $mail = $notification->toMail($this->studentAccount);

        $this->assertContains('mail', $notification->via($this->studentAccount));
        $this->assertSame('TWR Practical Exam Cancelled', $mail->subject);
        $this->assertSame('emails.training.exams.exam_cancelled_student', $mail->view);
        $this->assertSame(
            ['recipient', 'examBooking'],
            array_keys($mail->viewData),
        );
        $this->assertSame($this->examBooking->id, $mail->viewData['examBooking']->id);

        $html = View::make($mail->view, $mail->data())->render();

        $this->assertStringContainsString('Dear Alex Student', $html);
        $this->assertStringContainsString('Your TWR practical exam', $html);
        $this->assertStringContainsString('EGKK_TWR', $html);
        $this->assertStringContainsString('has been successfully cancelled', $html);
    }

    #[Test]
    public function examiner_notified_of_student_cancel_notification_includes_reason(): void
    {
        $notification = new ExamCancelledExaminerNotification($this->examBooking, 'Cannot make the date.');
        $mail = $notification->toMail($this->examinerAccount);

        $this->assertSame('TWR Practical Exam Cancelled', $mail->subject);
        $this->assertSame('emails.training.exams.exam_cancelled_examiner', $mail->view);
        $this->assertSame(
            ['recipient', 'examBooking', 'reason'],
            array_keys($mail->viewData),
        );
        $this->assertSame('Cannot make the date.', $mail->viewData['reason']);

        $html = View::make($mail->view, $mail->data())->render();

        $this->assertStringContainsString('Alex Student', $html);
        $this->assertStringContainsString((string) $this->studentAccount->id, $html);
        $this->assertStringContainsString('has cancelled their TWR practical exam', $html);
        $this->assertStringContainsString('Cannot make the date.', $html);
    }

    #[Test]
    public function student_notified_of_examiner_cancel_notification_uses_expected_subject_view_and_data(): void
    {
        $notification = new ExamCancelledByExaminerStudentNotification(
            $this->examBooking,
            $this->examinerAccount,
        );
        $mail = $notification->toMail($this->studentAccount);

        $this->assertSame('Your practical exam has been cancelled', $mail->subject);
        $this->assertSame('emails.training.exams.exam_cancelled_by_examiner_student', $mail->view);
        $this->assertSame(
            ['recipient', 'examBooking', 'cancelledByExaminer'],
            array_keys($mail->viewData),
        );
        $this->assertSame($this->examinerAccount->id, $mail->viewData['cancelledByExaminer']->id);

        $html = View::make($mail->view, $mail->data())->render();

        $this->assertStringContainsString('Jamie Examiner', $html);
        $this->assertStringContainsString((string) $this->examinerAccount->id, $html);
        $this->assertStringContainsString('has cancelled your TWR practical exam', $html);
        $this->assertStringContainsString('EGKK_TWR', $html);
        $this->assertStringContainsString('remain in the system', $html);
    }

    #[Test]
    public function co_examiner_notified_of_examiner_cancel_notification_uses_expected_subject_view_and_data(): void
    {
        $coExaminer = Account::factory()->create([
            'name_first' => 'Sam',
            'name_last' => 'Co-Examiner',
        ]);

        $notification = new ExamSessionCancelledForCoExaminerNotification(
            $this->examBooking,
            $this->examinerAccount,
        );
        $mail = $notification->toMail($coExaminer);

        $this->assertSame('TWR Practical Exam Session Cancelled', $mail->subject);
        $this->assertSame('emails.training.exams.exam_session_cancelled_for_co_examiner', $mail->view);
        $this->assertSame(
            ['recipient', 'examBooking', 'cancelledByExaminer'],
            array_keys($mail->viewData),
        );

        $html = View::make($mail->view, $mail->data())->render();

        $this->assertStringContainsString('Jamie Examiner', $html);
        $this->assertStringContainsString('Alex Student', $html);
        $this->assertStringContainsString('practical exam for Alex Student', $html);
        $this->assertStringContainsString("the student's exam request will remain", $html);
    }
}
