<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Exams;

use App\Events\Training\Exams\ExamAccepted;
use App\Listeners\Training\Exams\CreateCtsBookingEntry;
use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalExaminers;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateCtsBookingEntryTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_creates_booking_entry_when_exam_accepted(): void
    {
        $studentAccount = Account::factory()->create();
        $examinerAccount = Account::factory()->create();
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

        $this->assertDatabaseMissing('bookings', [
            'member_id' => $studentAccount->id,
            'type' => Booking::TYPE_EXAM,
        ]);

        $event = new ExamAccepted($examBooking);
        $listener = new CreateCtsBookingEntry;
        $listener->handle($event);

        $this->assertDatabaseHas('bookings', [
            'position_id' => $position->id,
            'member_id' => $studentAccount->id,
            'type' => Booking::TYPE_EXAM,
            'bookable_type' => ExamBooking::class,
            'bookable_id' => $examBooking->id,
        ]);
    }

    #[Test]
    public function it_handles_unknown_position_gracefully(): void
    {
        $studentAccount = Account::factory()->create();
        $student = Member::factory()->create([
            'id' => $studentAccount->id,
            'cid' => $studentAccount->id,
        ]);

        $examBooking = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'TWR',
            'position_1' => 'EG99_TWR',
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '14:00:00',
            'taken_to' => '15:30:00',
        ]);

        $event = new ExamAccepted($examBooking);
        $listener = new CreateCtsBookingEntry;
        $listener->handle($event);

        $this->assertDatabaseHas('bookings', [
            'member_id' => $studentAccount->id,
            'position_id' => null,
        ]);
    }

    #[Test]
    public function it_creates_booking_entry_with_correct_timestamps(): void
    {
        $studentAccount = Account::factory()->create();
        $student = Member::factory()->create([
            'id' => $studentAccount->id,
            'cid' => $studentAccount->id,
        ]);

        $examDate = Carbon::parse('2024-12-25');
        $examBooking = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'APP',
            'position_1' => 'EGLL_APP',
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'taken_date' => $examDate->format('Y-m-d'),
            'taken_from' => '09:30:00',
            'taken_to' => '11:00:00',
        ]);

        $event = new ExamAccepted($examBooking);
        $listener = new CreateCtsBookingEntry;

        $beforeTime = now();
        $listener->handle($event);
        $afterTime = now();

        $booking = Booking::where('bookable_type', ExamBooking::class)
            ->where('bookable_id', $examBooking->id)
            ->first();

        $this->assertNotNull($booking);
        $this->assertEquals($examDate->format('Y-m-d'), $booking->starts_at->format('Y-m-d'));
        $this->assertEquals('09:30', $booking->starts_at->format('H:i'));
        $this->assertEquals('11:00', $booking->ends_at->format('H:i'));
        $this->assertGreaterThanOrEqual($beforeTime, $booking->created_at);
        $this->assertLessThanOrEqual($afterTime, $booking->created_at);
    }

    #[Test]
    public function it_creates_booking_entry_for_different_exam_types(): void
    {
        $examTypes = [
            ['exam' => 'OBS', 'position' => 'EGKK_GND'],
            ['exam' => 'TWR', 'position' => 'EGLL_TWR'],
            ['exam' => 'APP', 'position' => 'EGCC_APP'],
            ['exam' => 'CTR', 'position' => 'LON_SC_CTR'],
        ];

        foreach ($examTypes as $index => $examData) {
            $position = Position::factory()->create(['callsign' => $examData['position']]);

            $studentAccount = Account::factory()->create();
            $student = Member::factory()->create([
                'id' => $studentAccount->id,
                'cid' => $studentAccount->id,
            ]);

            $examDate = Carbon::tomorrow()->addDays($index);
            $examBooking = ExamBooking::factory()->create([
                'student_id' => $student->id,
                'exam' => $examData['exam'],
                'position_1' => $examData['position'],
                'taken' => 1,
                'finished' => ExamBooking::NOT_FINISHED_FLAG,
                'taken_date' => $examDate->format('Y-m-d'),
                'taken_from' => '10:00:00',
                'taken_to' => '11:30:00',
            ]);

            $event = new ExamAccepted($examBooking);
            $listener = new CreateCtsBookingEntry;
            $listener->handle($event);

            $this->assertDatabaseHas('bookings', [
                'position_id' => $position->id,
                'member_id' => $studentAccount->id,
                'type' => Booking::TYPE_EXAM,
                'bookable_id' => $examBooking->id,
            ]);
        }
    }

    #[Test]
    public function it_links_booking_to_exam_booking_via_bookable(): void
    {
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
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '16:00:00',
            'taken_to' => '17:00:00',
        ]);

        $event = new ExamAccepted($examBooking);
        $listener = new CreateCtsBookingEntry;
        $listener->handle($event);

        $booking = Booking::where('type', Booking::TYPE_EXAM)
            ->where('member_id', $studentAccount->id)
            ->first();

        $this->assertNotNull($booking);
        $this->assertEquals($examBooking->id, $booking->bookable_id);
        $this->assertEquals(ExamBooking::class, $booking->bookable_type);
    }

    #[Test]
    public function it_handles_multiple_exam_bookings_for_same_student(): void
    {
        $studentAccount = Account::factory()->create();
        $student = Member::factory()->create([
            'id' => $studentAccount->id,
            'cid' => $studentAccount->id,
        ]);

        $position1 = Position::factory()->create(['callsign' => 'EGKK_TWR']);
        $position2 = Position::factory()->create(['callsign' => 'EGLL_APP']);

        $examBooking1 = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'TWR',
            'position_1' => 'EGKK_TWR',
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '11:00:00',
        ]);

        $examBooking2 = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'APP',
            'position_1' => 'EGLL_APP',
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'taken_date' => Carbon::tomorrow()->addDay()->format('Y-m-d'),
            'taken_from' => '14:00:00',
            'taken_to' => '15:30:00',
        ]);

        $listener = new CreateCtsBookingEntry;
        $listener->handle(new ExamAccepted($examBooking1));
        $listener->handle(new ExamAccepted($examBooking2));

        $this->assertDatabaseHas('bookings', [
            'position_id' => $position1->id,
            'member_id' => $studentAccount->id,
            'type' => Booking::TYPE_EXAM,
            'bookable_id' => $examBooking1->id,
        ]);

        $this->assertDatabaseHas('bookings', [
            'position_id' => $position2->id,
            'member_id' => $studentAccount->id,
            'type' => Booking::TYPE_EXAM,
            'bookable_id' => $examBooking2->id,
        ]);

        $bookingCount = Booking::where('member_id', $studentAccount->id)
            ->where('type', Booking::TYPE_EXAM)
            ->count();

        $this->assertEquals(2, $bookingCount);
    }
}
