<?php

namespace Tests\Feature\Training\Exams;

use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamSetup;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalExaminers;
use App\Models\Mship\Account;
use App\Notifications\Training\Exams\ExamCancelledExaminerNotification;
use App\Notifications\Training\Exams\ExamCancelledStudentNotification;
use App\Services\Training\CancelPendingExamService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CancelPendingExamTest extends TestCase
{
    use DatabaseTransactions;

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
        $this->service->cancel($this->examBooking, 'Cannot make it.', $this->studentAccount);

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
        $this->service->cancel($this->examBooking, 'Cannot make it.', $this->studentAccount);
        $this->examSetup->refresh();

        $this->assertEquals(0, $this->examSetup->booked);
    }

    #[Test]
    public function it_deletes_practical_examiner_record(): void
    {
        $this->service->cancel($this->examBooking, 'Cannot make it.', $this->studentAccount);

        $this->assertDatabaseMissing('practical_examiners', ['examid' => $this->examBooking->id], 'cts');
    }

    #[Test]
    public function it_inserts_cancel_reason_record(): void
    {
        $reason = 'I have a scheduling conflict.';

        $this->service->cancel($this->examBooking, $reason, $this->studentAccount);

        $this->assertDatabaseHas('cancel_reason', [
            'sesh_id' => $this->examBooking->id,
            'sesh_type' => 'EX',
            'reason' => $reason,
            'used' => 0,
            'reason_by' => $this->studentAccount->id], 'cts');
    }

    #[Test]
    public function it_sends_student_cancellation_notification(): void
    {
        Notification::fake();
        $this->service->cancel($this->examBooking, 'Cannot make it.', $this->studentAccount);

        Notification::assertSentTo($this->studentAccount, ExamCancelledStudentNotification::class);
    }

    #[Test]
    public function it_sends_examiner_cancellation_notification(): void
    {
        Notification::fake();
        $this->service->cancel($this->examBooking, 'Cannot make it.', $this->studentAccount);

        Notification::assertSentTo($this->examinerAccount, ExamCancelledExaminerNotification::class);
    }
}
