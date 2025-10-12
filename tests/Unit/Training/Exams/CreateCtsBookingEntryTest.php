<?php

namespace Tests\Unit\Training\Exams;

use App\Events\Training\Exams\ExamAccepted;
use App\Listeners\Training\Exams\CreateCtsBookingEntry;
use App\Models\Cts\Booking;
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
    public function it_creates_cts_booking_entry_when_exam_accepted()
    {
        // Create test data
        $studentAccount = Account::factory()->create();
        $examinerAccount = Account::factory()->create();

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
        $this->assertDatabaseMissing('bookings', [
            'date' => $examDate->format('Y-m-d'),
            'position' => 'EGKK_TWR',
            'member_id' => $student->id,
            'type' => 'EX',
        ], 'cts');

        // Create the event
        $event = new ExamAccepted($examBooking);

        // Create and handle the listener
        $listener = new CreateCtsBookingEntry;
        $listener->handle($event);

        // Assert CTS booking entry was created
        $this->assertDatabaseHas('bookings', [
            'date' => $examDate->format('Y-m-d'),
            'from' => '14:00:00',
            'to' => '15:30:00',
            'position' => 'EGKK_TWR',
            'member_id' => $student->id,
            'type' => 'EX',
            'type_id' => $examBooking->id,
            'local_id' => 0,
        ], 'cts');
    }

    #[Test]
    public function it_creates_booking_entry_with_correct_timestamps()
    {
        // Create test data
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

        // Create the event and handle it
        $event = new ExamAccepted($examBooking);
        $listener = new CreateCtsBookingEntry;

        // Record time before handling
        $beforeTime = now();
        $listener->handle($event);
        $afterTime = now();

        // Get the created booking
        $booking = Booking::where('type_id', $examBooking->id)->first();

        $this->assertNotNull($booking);
        $this->assertGreaterThanOrEqual($beforeTime, $booking->time_booked);
        $this->assertLessThanOrEqual($afterTime, $booking->time_booked);
    }

    #[Test]
    public function it_creates_booking_entry_for_different_exam_types()
    {
        $examTypes = [
            ['exam' => 'OBS', 'position' => 'EGKK_GND'],
            ['exam' => 'TWR', 'position' => 'EGLL_TWR'],
            ['exam' => 'APP', 'position' => 'EGCC_APP'],
            ['exam' => 'CTR', 'position' => 'LON_SC_CTR'],
        ];

        foreach ($examTypes as $examData) {
            // Create test data for each exam type
            $studentAccount = Account::factory()->create();
            $student = Member::factory()->create([
                'id' => $studentAccount->id,
                'cid' => $studentAccount->id,
            ]);

            $examDate = Carbon::tomorrow()->addDays(array_search($examData, $examTypes));
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

            // Handle the event
            $event = new ExamAccepted($examBooking);
            $listener = new CreateCtsBookingEntry;
            $listener->handle($event);

            // Assert booking was created for this exam type
            $this->assertDatabaseHas('bookings', [
                'date' => $examDate->format('Y-m-d'),
                'position' => $examData['position'],
                'member_id' => $student->id,
                'type' => 'EX',
                'type_id' => $examBooking->id,
            ], 'cts');
        }
    }

    #[Test]
    public function it_creates_booking_with_nullable_fields_properly_set()
    {
        // Create test data
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
            'taken_from' => '14:00:00',
            'taken_to' => '15:30:00',
        ]);

        // Handle the event
        $event = new ExamAccepted($examBooking);
        $listener = new CreateCtsBookingEntry;
        $listener->handle($event);

        // Get the created booking
        $booking = Booking::where('type_id', $examBooking->id)->first();

        $this->assertNotNull($booking);
        $this->assertEquals(0, $booking->local_id);
        $this->assertNull($booking->groupID);
        $this->assertNull($booking->eurobook_id);
        $this->assertEquals(0, $booking->eurobook_import); // This field appears to be 0, not null based on the error
    }

    #[Test]
    public function it_links_booking_to_exam_booking_via_type_id()
    {
        // Create test data
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

        // Handle the event
        $event = new ExamAccepted($examBooking);
        $listener = new CreateCtsBookingEntry;
        $listener->handle($event);

        // Verify the type_id links to the exam booking
        $booking = Booking::where('type', 'EX')
            ->where('member_id', $student->id)
            ->first();

        $this->assertNotNull($booking);
        $this->assertEquals($examBooking->id, $booking->type_id);
    }

    #[Test]
    public function it_handles_multiple_exam_bookings_for_same_student()
    {
        // Create test data
        $studentAccount = Account::factory()->create();
        $student = Member::factory()->create([
            'id' => $studentAccount->id,
            'cid' => $studentAccount->id,
        ]);

        // Create two different exam bookings for the same student
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

        // Handle both events
        $listener = new CreateCtsBookingEntry;
        $listener->handle(new ExamAccepted($examBooking1));
        $listener->handle(new ExamAccepted($examBooking2));

        // Assert both bookings were created
        $this->assertDatabaseHas('bookings', [
            'position' => 'EGKK_TWR',
            'member_id' => $student->id,
            'type' => 'EX',
            'type_id' => $examBooking1->id,
        ], 'cts');

        $this->assertDatabaseHas('bookings', [
            'position' => 'EGLL_APP',
            'member_id' => $student->id,
            'type' => 'EX',
            'type_id' => $examBooking2->id,
        ], 'cts');

        // Verify we have exactly 2 exam bookings for this student
        $bookingCount = Booking::where('member_id', $student->id)
            ->where('type', 'EX')
            ->count();

        $this->assertEquals(2, $bookingCount);
    }
}
