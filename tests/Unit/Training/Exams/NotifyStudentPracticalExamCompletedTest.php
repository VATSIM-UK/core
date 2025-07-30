<?php

namespace Tests\Unit\Training\Exams;

use App\Events\Training\Exams\PracticalExamCompleted;
use App\Listeners\Training\Exams\NotifyStudentPracticalExamCompleted;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;
use App\Models\Mship\Account;
use App\Notifications\Training\Exams\PracticalExamResultNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotifyStudentPracticalExamCompletedTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_sends_notification_to_student_when_exam_completed()
    {
        Notification::fake();

        // Create test data
        $studentAccount = Account::factory()->create(['email' => 'student@example.com']);
        $examinerAccount = Account::factory()->create(['email' => 'examiner@example.com']);

        $student = Member::factory()->create(['cid' => $studentAccount->id]);
        $examiner = Member::factory()->create(['cid' => $examinerAccount->id]);

        $examBooking = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'TWR',
            'position_1' => 'EGLL_TWR',
            'taken' => 1,
            'finished' => ExamBooking::FINISHED_FLAG,
        ]);

        $examBooking->examiners()->create([
            'examid' => $examBooking->id,
            'senior' => $examiner->id,
        ]);

        $practicalResult = PracticalResult::factory()->create([
            'examid' => $examBooking->id,
            'student_id' => $student->id,
            'exam' => 'TWR',
            'result' => PracticalResult::PASSED,
            'notes' => 'Excellent performance',
            'date' => now(),
        ]);

        // Create the event
        $event = new PracticalExamCompleted($examBooking, $practicalResult);

        // Create and handle the listener
        $listener = new NotifyStudentPracticalExamCompleted;
        $listener->handle($event);

        // Assert notification was sent to the student
        Notification::assertSentTo(
            $studentAccount,
            PracticalExamResultNotification::class
        );
    }

    #[Test]
    public function it_does_not_send_notification_when_student_has_no_email()
    {
        Notification::fake();

        // Create test data with no email
        $studentAccount = Account::factory()->create(['email' => null]);
        $examinerAccount = Account::factory()->create(['email' => 'examiner@example.com']);

        $student = Member::factory()->create(['cid' => $studentAccount->id]);
        $examiner = Member::factory()->create(['cid' => $examinerAccount->id]);

        $examBooking = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'TWR',
            'position_1' => 'EGLL_TWR',
            'taken' => 1,
            'finished' => ExamBooking::FINISHED_FLAG,
        ]);

        $examBooking->examiners()->create([
            'examid' => $examBooking->id,
            'senior' => $examiner->id,
        ]);

        $practicalResult = PracticalResult::factory()->create([
            'examid' => $examBooking->id,
            'student_id' => $student->id,
            'exam' => 'TWR',
            'result' => PracticalResult::PASSED,
            'notes' => 'Excellent performance',
            'date' => now(),
        ]);

        // Create the event
        $event = new PracticalExamCompleted($examBooking, $practicalResult);

        // Create and handle the listener
        $listener = new NotifyStudentPracticalExamCompleted;
        $listener->handle($event);

        // Assert no notification was sent
        Notification::assertNotSentTo(
            $studentAccount,
            PracticalExamResultNotification::class
        );
    }
}
