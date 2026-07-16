<?php

declare(strict_types=1);

namespace Tests\Feature\Training\Exams;

use App\Events\Training\Exams\ExamAccepted;
use App\Models\Atc\Position;
use App\Models\Booking;
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
    public function it_triggers_all_listeners_when_exam_accepted_event_is_fired(): void
    {
        Notification::fake();

        $studentAccount = Account::factory()->create(['email' => 'student@test.com']);
        $examinerAccount = Account::factory()->create(['email' => 'examiner@test.com']);
        $position = Position::factory()->create(['callsign' => 'EGKK_TWR']);

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

        $examBooking->load(['student', 'examiners.primaryExaminer']);

        $bookingCountBefore = Booking::where('type', Booking::TYPE_EXAM)
            ->where('member_id', $studentAccount->id)
            ->count();

        $this->assertEquals(0, $bookingCountBefore);

        event(new ExamAccepted($examBooking));

        Notification::assertSentTo(
            $studentAccount,
            ExamAcceptedStudentNotification::class
        );

        Notification::assertSentTo(
            $examinerAccount,
            ExamAcceptedExaminerNotification::class
        );

        $this->assertDatabaseHas('bookings', [
            'position_id' => $position->id,
            'member_id' => $studentAccount->id,
            'type' => Booking::TYPE_EXAM,
            'bookable_id' => $examBooking->id,
        ]);

        $bookingCountAfter = Booking::where('type', Booking::TYPE_EXAM)
            ->where('member_id', $studentAccount->id)
            ->count();

        $this->assertEquals(1, $bookingCountAfter);
    }

    #[Test]
    public function it_handles_exam_with_secondary_examiner(): void
    {
        Notification::fake();

        $studentAccount = Account::factory()->create(['email' => 'student@test.com']);
        $primaryExaminerAccount = Account::factory()->create(['email' => 'primary@test.com']);
        $secondaryExaminerAccount = Account::factory()->create(['email' => 'secondary@test.com']);
        $position = Position::factory()->create(['callsign' => 'EGLL_APP']);

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

        $examBooking->load(['student', 'examiners.primaryExaminer', 'examiners.secondaryExaminer']);

        event(new ExamAccepted($examBooking));

        Notification::assertSentTo($studentAccount, ExamAcceptedStudentNotification::class);
        Notification::assertSentTo($primaryExaminerAccount, ExamAcceptedExaminerNotification::class);
        Notification::assertSentTo($secondaryExaminerAccount, ExamAcceptedExaminerNotification::class);

        $this->assertDatabaseHas('bookings', [
            'position_id' => $position->id,
            'member_id' => $studentAccount->id,
            'type' => Booking::TYPE_EXAM,
            'bookable_id' => $examBooking->id,
        ]);
    }

    #[Test]
    public function it_can_be_tested_with_event_fake(): void
    {
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

        event(new ExamAccepted($examBooking));

        Event::assertDispatched(ExamAccepted::class, function ($event) use ($examBooking) {
            return $event->examBooking->id === $examBooking->id;
        });

        $this->assertDatabaseMissing('bookings', [
            'type' => Booking::TYPE_EXAM,
            'member_id' => $studentAccount->id,
        ]);
    }
}
