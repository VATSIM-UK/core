<?php

namespace Tests\Unit\Training\Exams;

use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamSetup;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalExaminers;
use App\Models\Mship\Account;
use App\Notifications\Training\Exams\ExamCancelledByExaminerStudentNotification;
use App\Notifications\Training\Exams\ExamCancelledExaminerNotification;
use App\Notifications\Training\Exams\ExamCancelledStudentNotification;
use App\Notifications\Training\Exams\ExamSessionCancelledForCoExaminerNotification;
use App\Services\Training\CancelPendingExamService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CancelPendingExamTest extends TestCase
{
    private CancelPendingExamService $service;

    protected Account $studentAccount;

    protected Member $studentMember;

    protected Account $examinerAccount;

    protected Member $examinerMember;

    protected ExamBooking $examBooking;

    protected ExamSetup $examSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CancelPendingExamService;

        $this->studentAccount = Account::factory()->create();
        $this->studentMember = Member::factory()->create([
            'id' => $this->studentAccount->id,
            'cid' => $this->studentAccount->id,
        ]);

        $this->examinerAccount = Account::factory()->create();
        $this->examinerMember = Member::factory()->create([
            'id' => $this->examinerAccount->id,
            'cid' => $this->examinerAccount->id,
            'examiner' => true,
        ]);

        $this->examBooking = ExamBooking::factory()->create([
            'student_id' => $this->studentMember->id,
            'taken' => 1,
            'taken_date' => now()->addDays(3)->format('Y-m-d'),
            'taken_from' => '14:00:00',
            'taken_to' => '16:00:00',
            'exmr_id' => $this->examinerMember->id,
            'exmr_rating' => 3,
            'time_book' => now(),
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'TWR',
            'position_1' => 'EGKK_TWR',
            'student_rating' => 1,
        ]);

        $this->examSetup = ExamSetup::create([
            'rts_id' => 1,
            'student_id' => $this->studentMember->id,
            'position_1' => 'EGKK_TWR',
            'exam' => 'TWR',
            'bookid' => $this->examBooking->id,
            'booked' => 1,
        ]);

        PracticalExaminers::create([
            'examid' => $this->examBooking->id,
            'senior' => $this->examinerMember->id,
        ]);
    }

    #[Test]
    public function it_resets_booking_taken_fields_to_pre_acceptance_state(): void
    {
        $this->service->cancelByStudent($this->examBooking, 'Cannot make it.', $this->studentAccount);

        $this->examBooking->refresh();

        $this->assertEquals(0, $this->examBooking->taken);
        $this->assertNull($this->examBooking->taken_date);
        $this->assertNull($this->examBooking->taken_from);
        $this->assertNull($this->examBooking->taken_to);
        $this->assertNull($this->examBooking->exmr_id);
        $this->assertNull($this->examBooking->exmr_rating);
        $this->assertNull($this->examBooking->time_book);
    }

    #[Test]
    public function it_resets_exam_setup_booked_flag(): void
    {
        $this->service->cancelByStudent($this->examBooking, 'Cannot make it.', $this->studentAccount);
        $this->examSetup->refresh();

        $this->assertEquals(0, $this->examSetup->booked);
    }

    #[Test]
    public function it_deletes_practical_examiner_record(): void
    {
        $this->service->cancelByStudent($this->examBooking, 'Cannot make it.', $this->studentAccount);

        $this->assertDatabaseMissing('practical_examiners', ['examid' => $this->examBooking->id], 'cts');
    }

    #[Test]
    public function it_inserts_cancel_reason_record(): void
    {
        $reason = 'I have a scheduling conflict.';

        $this->service->cancelByStudent($this->examBooking, $reason, $this->studentAccount);

        $this->assertDatabaseHas('cancel_reason', [
            'sesh_id' => $this->examBooking->id,
            'sesh_type' => 'EX',
            'reason' => $reason,
            'used' => 0,
            'reason_by' => $this->studentAccount->id], 'cts');
    }

    #[Test]
    public function it_sends_student_cancellation_notification_with_expected_mail_content(): void
    {
        Notification::fake();
        $this->service->cancelByStudent($this->examBooking, 'Cannot make it.', $this->studentAccount);

        Notification::assertSentTo(
            $this->studentAccount,
            ExamCancelledStudentNotification::class,
            function (ExamCancelledStudentNotification $notification): bool {
                $mail = $notification->toMail($this->studentAccount);
                $html = View::make($mail->view, $mail->data())->render();

                return $mail->viewData['examBooking']->id === $this->examBooking->id
                    && str_contains($html, 'has been successfully cancelled');
            },
        );
    }

    #[Test]
    public function it_sends_examiner_cancellation_notification_with_reason_in_mail(): void
    {
        Notification::fake();
        $reason = 'Cannot make it.';

        $this->service->cancelByStudent($this->examBooking, $reason, $this->studentAccount);

        Notification::assertSentTo(
            $this->examinerAccount,
            ExamCancelledExaminerNotification::class,
            function (ExamCancelledExaminerNotification $notification) use ($reason): bool {
                $mail = $notification->toMail($this->examinerAccount);

                return $mail->viewData['reason'] === $reason
                    && $mail->viewData['examBooking']->id === $this->examBooking->id;
            },
        );
    }

    #[Test]
    public function it_throws_when_stranger_attempts_cancel_by_student(): void
    {
        $stranger = Account::factory()->create();

        $this->expectException(AuthorizationException::class);
        $this->service->cancelByStudent($this->examBooking, 'No access.', $stranger);
    }

    #[Test]
    public function it_throws_when_stranger_attempts_cancel_by_examiner(): void
    {
        $stranger = Account::factory()->create();

        $this->expectException(AuthorizationException::class);
        $this->service->cancelByExaminer($this->examBooking, 'No access.', $stranger);
    }

    #[Test]
    public function it_throws_when_examiner_uses_cancel_by_student(): void
    {
        $this->examinerAccount->givePermissionTo('training.exams.conduct.twr');

        $this->expectException(AuthorizationException::class);
        $this->service->cancelByStudent($this->examBooking, 'Wrong API.', $this->examinerAccount);
    }

    #[Test]
    public function it_throws_when_student_uses_cancel_by_examiner(): void
    {
        $this->expectException(AuthorizationException::class);
        $this->service->cancelByExaminer($this->examBooking, 'Wrong API.', $this->studentAccount);
    }

    #[Test]
    public function it_throws_when_assigned_examiner_lacks_conduct_permission(): void
    {
        $this->expectException(AuthorizationException::class);
        $this->service->cancelByExaminer($this->examBooking, 'No permission.', $this->examinerAccount);
    }

    #[Test]
    public function it_sends_examiner_initiated_notifications_to_student_and_co_examiner(): void
    {
        $coExaminerAccount = Account::factory()->create();
        Member::factory()->create([
            'id' => $coExaminerAccount->id,
            'cid' => $coExaminerAccount->id,
        ]);
        $this->examBooking->examiners->update(['other' => $coExaminerAccount->id]);

        Notification::fake();
        $this->examinerAccount->givePermissionTo('training.exams.conduct.twr');
        $this->service->cancelByExaminer($this->examBooking, 'Department decision.', $this->examinerAccount);

        Notification::assertSentTo(
            $this->studentAccount,
            ExamCancelledByExaminerStudentNotification::class,
            function (ExamCancelledByExaminerStudentNotification $notification): bool {
                $mail = $notification->toMail($this->studentAccount);

                return $mail->subject === 'Your practical exam has been cancelled'
                    && $mail->viewData['cancelledByExaminer']->id === $this->examinerAccount->id
                    && $mail->viewData['examBooking']->id === $this->examBooking->id;
            },
        );
        Notification::assertSentTo(
            $coExaminerAccount,
            ExamSessionCancelledForCoExaminerNotification::class,
            function (ExamSessionCancelledForCoExaminerNotification $notification) use ($coExaminerAccount): bool {
                $mail = $notification->toMail($coExaminerAccount);

                return $mail->viewData['cancelledByExaminer']->id === $this->examinerAccount->id;
            },
        );
        Notification::assertNotSentTo($this->examinerAccount, ExamSessionCancelledForCoExaminerNotification::class);
        Notification::assertNotSentTo($this->examinerAccount, ExamCancelledExaminerNotification::class);
        Notification::assertNotSentTo($this->studentAccount, ExamCancelledStudentNotification::class);
    }

    #[Test]
    public function it_resets_booking_when_cancelled_by_examiner(): void
    {
        $this->examinerAccount->givePermissionTo('training.exams.conduct.twr');
        $this->service->cancelByExaminer($this->examBooking, 'Weather.', $this->examinerAccount);

        $this->examBooking->refresh();

        $this->assertEquals(0, $this->examBooking->taken);
    }
}
