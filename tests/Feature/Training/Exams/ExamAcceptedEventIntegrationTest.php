<?php

namespace Tests\Feature\Training\Exams;

use App\Events\Training\Exams\ExamAccepted;
use App\Models\Cts\Booking;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalExaminers;
use App\Models\Mship\Account;
use App\Notifications\Training\Exams\ExamAcceptedExaminerNotification;
use App\Notifications\Training\Exams\ExamAcceptedStudentNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExamAcceptedEventIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_triggers_all_listeners_when_exam_accepted_event_is_fired()
    {
        // Fake notifications but allow events to fire normally
        Notification::fake();

        // Create test data
        $studentAccount = Account::factory()->create(['email' => 'student@test.com']);
        $examinerAccount = Account::factory()->create(['email' => 'examiner@test.com']);

        $student = Member::factory()->create([
            'id' => $studentAccount->id,
            'cid' => $studentAccount->id,
        ]);
        $examiner = Member::factory()->create([
            'id' => $examinerAccount->id,
            'cid' => $examinerAccount->id,
        ]);

        $examDate = Carbon::tomorrow();
        $examBooking = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'TWR',
            'position_1' => 'EGKK_TWR',
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'taken_date' => $examDate->format('Y-m-d'),
            'taken_from' => '14:00:00',
            'taken_to' => '15:30:00',
            'student_rating' => 2,
        ]);

        $practicalExaminers = PracticalExaminers::create([
            'examid' => $examBooking->id,
            'senior' => $examiner->id,
            'other' => null,
            'trainee' => null,
        ]);

        // Load relationships
        $examBooking->load(['student', 'examiners.primaryExaminer']);

        // Verify no CTS booking exists before the event
        $bookingCountBefore = Booking::where('type', 'EX')
            ->where('position', 'EGKK_TWR')
            ->where('member_id', $student->id)
            ->count();

        $this->assertEquals(0, $bookingCountBefore);

        // Fire the event
        event(new ExamAccepted($examBooking));

        // Assert all three listeners were triggered:

        // 1. Student notification was sent
        Notification::assertSentTo(
            $studentAccount,
            ExamAcceptedStudentNotification::class
        );

        // 2. Examiner notification was sent
        Notification::assertSentTo(
            $examinerAccount,
            ExamAcceptedExaminerNotification::class
        );

        // 3. CTS booking entry was created
        $this->assertDatabaseHas('bookings', [
            'date' => $examDate->format('Y-m-d'),
            'from' => '14:00:00',
            'to' => '15:30:00',
            'position' => 'EGKK_TWR',
            'member_id' => $student->id,
            'type' => 'EX',
            'type_id' => $examBooking->id,
        ], 'cts');

        // Verify exactly one CTS booking was created
        $bookingCountAfter = Booking::where('type', 'EX')
            ->where('position', 'EGKK_TWR')
            ->where('member_id', $student->id)
            ->count();

        $this->assertEquals(1, $bookingCountAfter);
    }

    #[Test]
    public function it_handles_exam_with_secondary_examiner()
    {
        // Fake notifications but allow events to fire normally
        Notification::fake();

        // Create test data with primary and secondary examiners
        $studentAccount = Account::factory()->create(['email' => 'student@test.com']);
        $primaryExaminerAccount = Account::factory()->create(['email' => 'primary@test.com']);
        $secondaryExaminerAccount = Account::factory()->create(['email' => 'secondary@test.com']);

        $student = Member::factory()->create([
            'id' => $studentAccount->id,
            'cid' => $studentAccount->id,
        ]);
        $primaryExaminer = Member::factory()->create([
            'id' => $primaryExaminerAccount->id,
            'cid' => $primaryExaminerAccount->id,
        ]);
        $secondaryExaminer = Member::factory()->create([
            'id' => $secondaryExaminerAccount->id,
            'cid' => $secondaryExaminerAccount->id,
        ]);

        $examDate = Carbon::tomorrow();
        $examBooking = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'APP',
            'position_1' => 'EGLL_APP',
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'taken_date' => $examDate->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        $practicalExaminers = PracticalExaminers::create([
            'examid' => $examBooking->id,
            'senior' => $primaryExaminer->id,
            'other' => $secondaryExaminer->id,
            'trainee' => null,
        ]);

        // Load relationships
        $examBooking->load(['student', 'examiners.primaryExaminer', 'examiners.secondaryExaminer']);

        // Fire the event
        event(new ExamAccepted($examBooking));

        // Assert notifications were sent to all parties
        Notification::assertSentTo($studentAccount, ExamAcceptedStudentNotification::class);
        Notification::assertSentTo($primaryExaminerAccount, ExamAcceptedExaminerNotification::class);
        Notification::assertSentTo($secondaryExaminerAccount, ExamAcceptedExaminerNotification::class);

        // Assert CTS booking was created
        $this->assertDatabaseHas('bookings', [
            'date' => $examDate->format('Y-m-d'),
            'position' => 'EGLL_APP',
            'member_id' => $student->id,
            'type' => 'EX',
            'type_id' => $examBooking->id,
        ], 'cts');
    }

    #[Test]
    public function it_can_be_tested_with_event_fake()
    {
        // Test that the event can be properly faked when needed
        Event::fake([ExamAccepted::class]);

        $studentAccount = Account::factory()->create();
        $student = Member::factory()->create([
            'id' => $studentAccount->id,
            'cid' => $studentAccount->id,
        ]);

        $examBooking = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'TWR',
            'position_1' => 'EGKK_TWR',
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
        ]);

        // Fire the event
        event(new ExamAccepted($examBooking));

        // Assert the event was dispatched
        Event::assertDispatched(ExamAccepted::class, function ($event) use ($examBooking) {
            return $event->examBooking->id === $examBooking->id;
        });

        // When events are faked, listeners don't run, so no booking should be created
        $this->assertDatabaseMissing('bookings', [
            'type' => 'EX',
            'member_id' => $student->id,
        ], 'cts');
    }
}
