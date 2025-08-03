<?php

namespace Tests\Unit\Training\Exams;

use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;
use App\Models\Mship\Account;
use App\Notifications\Training\Exams\PracticalExamResultNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PracticalExamResultNotificationTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_sends_notification_to_student_with_correct_information()
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

        // Send notification
        $studentAccount->notify(new PracticalExamResultNotification($practicalResult));

        // Assert notification was sent
        Notification::assertSentTo(
            $studentAccount,
            PracticalExamResultNotification::class,
            function ($notification) use ($studentAccount) {
                $mailMessage = $notification->toMail($studentAccount);

                return $mailMessage->subject === 'Your TWR Practical Exam Result';
            }
        );
    }

    #[Test]
    public function it_handles_failed_exam_result()
    {
        Notification::fake();

        $studentAccount = Account::factory()->create(['email' => 'student@example.com']);
        $examinerAccount = Account::factory()->create(['email' => 'examiner@example.com']);

        $student = Member::factory()->create(['cid' => $studentAccount->id]);
        $examiner = Member::factory()->create(['cid' => $examinerAccount->id]);

        $examBooking = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'APP',
            'position_1' => 'EGLL_APP',
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
            'exam' => 'APP',
            'result' => PracticalResult::FAILED,
            'notes' => 'Need more practice with approach procedures',
            'date' => now(),
        ]);

        // Send notification
        $studentAccount->notify(new PracticalExamResultNotification($practicalResult));

        // Assert notification was sent with correct subject
        Notification::assertSentTo(
            $studentAccount,
            PracticalExamResultNotification::class,
            function ($notification) use ($studentAccount) {
                $mailMessage = $notification->toMail($studentAccount);

                return $mailMessage->subject === 'Your APP Practical Exam Result';
            }
        );
    }
}
