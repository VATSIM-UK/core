<?php

declare(strict_types=1);

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\Calendar;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member as CtsMember;
use App\Models\Cts\PracticalExaminers;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Repositories\Cts\BookingRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpcomingMentoringExamsTest extends TestCase
{
    use DatabaseTransactions;

    /** The abbreviated label the calendar shows: preferred first name, last initial. */
    private function expectedDisplayName(Account $account): string
    {
        return $account->name_preferred.' '.mb_substr($account->name_last, 0, 1).'.';
    }

    /**
     * Builds a mentoring booking the way MentoringSessionsService::createCoreBooking()
     * does in production: a CTS session, a mirrored CTS booking row for the callsign,
     * and a core Booking linked to both.
     *
     * @return array{booking: Booking, mentorAccount: Account, studentAccount: Account}
     */
    private function createMentoringBooking(Carbon $startsAt, string $callsign = 'EGLL_APP'): array
    {
        $studentAccount = Account::factory()->create();
        $mentorAccount = Account::factory()->create();
        $student = CtsMember::factory()->forAccount($studentAccount)->create();
        $mentor = CtsMember::factory()->forAccount($mentorAccount)->create();
        $endsAt = $startsAt->clone()->addHours(2);

        $session = Session::factory()->create([
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'position' => $callsign,
            'taken' => 1,
            'taken_date' => $startsAt->toDateString(),
            'taken_from' => $startsAt->format('H:i:s'),
            'taken_to' => $endsAt->format('H:i:s'),
        ]);

        $cts = CtsBooking::factory()->create([
            'type' => 'ME',
            'member_id' => $student->id,
            'position' => $callsign,
            'date' => $startsAt->toDateString(),
            'from' => $startsAt->format('H:i:s'),
            'to' => $endsAt->format('H:i:s'),
        ]);

        $booking = Booking::create([
            'position_id' => null,
            'member_id' => $studentAccount->id,
            'type' => Booking::TYPE_MENTORING,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'cts_booking_id' => $cts->id,
            'bookable_type' => Session::class,
            'bookable_id' => $session->id,
        ]);

        return ['booking' => $booking, 'mentorAccount' => $mentorAccount, 'studentAccount' => $studentAccount];
    }

    /**
     * Builds an exam booking the way CreateCtsBookingEntry does in production: a CTS
     * exam with its leading examiner, a mirrored CTS booking row, and a linked core
     * Booking.
     *
     * @return array{booking: Booking, examinerAccount: Account, studentAccount: Account}
     */
    private function createExamBooking(Carbon $startsAt, string $callsign = 'EGKK_TWR'): array
    {
        $studentAccount = Account::factory()->create();
        $examinerAccount = Account::factory()->create();
        $student = CtsMember::factory()->forAccount($studentAccount)->create();
        $examiner = CtsMember::factory()->forAccount($examinerAccount)->create();
        $endsAt = $startsAt->clone()->addHours(2);

        $exam = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'position_1' => $callsign,
            'taken_date' => $startsAt->toDateString(),
            'taken_from' => $startsAt->format('H:i:s'),
            'taken_to' => $endsAt->format('H:i:s'),
        ]);

        PracticalExaminers::create([
            'examid' => $exam->id,
            'senior' => $examiner->id,
            'other' => null,
            'trainee' => null,
        ]);

        $cts = CtsBooking::factory()->create([
            'type' => 'EX',
            'member_id' => $student->id,
            'position' => $callsign,
            'date' => $startsAt->toDateString(),
            'from' => $startsAt->format('H:i:s'),
            'to' => $endsAt->format('H:i:s'),
        ]);

        $booking = Booking::create([
            'position_id' => null,
            'member_id' => $studentAccount->id,
            'type' => Booking::TYPE_EXAM,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'cts_booking_id' => $cts->id,
            'bookable_type' => ExamBooking::class,
            'bookable_id' => $exam->id,
        ]);

        return ['booking' => $booking, 'examinerAccount' => $examinerAccount, 'studentAccount' => $studentAccount];
    }

    #[Test]
    public function it_returns_upcoming_mentoring_and_exam_bookings_in_chronological_order(): void
    {
        $exam = $this->createExamBooking(Carbon::tomorrow()->setTime(9, 0));
        $mentoring = $this->createMentoringBooking(Carbon::tomorrow()->addDay()->setTime(10, 0));

        // Excluded: in the past.
        $this->createMentoringBooking(Carbon::yesterday()->setTime(10, 0));

        // Excluded: standard booking, not mentoring/exam.
        Booking::create([
            'position_id' => null,
            'member_id' => Account::factory()->create()->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setTime(11, 0),
            'ends_at' => Carbon::tomorrow()->setTime(12, 0),
        ]);

        $results = app(BookingRepository::class)->getUpcomingMentoringAndExamBookings();

        $this->assertCount(2, $results);
        $this->assertSame('EX', $results[0]->type);
        $this->assertSame((string) $exam['booking']->id, $results[0]->id);
        $this->assertSame('ME', $results[1]->type);
        $this->assertSame((string) $mentoring['booking']->id, $results[1]->id);
    }

    #[Test]
    public function it_caps_results_at_the_given_limit(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $this->createMentoringBooking(Carbon::tomorrow()->addDays($i)->setTime(10, 0));
        }

        $results = app(BookingRepository::class)->getUpcomingMentoringAndExamBookings();

        $this->assertCount(10, $results);
        $this->assertSame(Carbon::tomorrow()->toDateString(), $results[0]->date, 'Must keep the earliest sessions');
        $this->assertSame(Carbon::tomorrow()->addDays(9)->toDateString(), $results[9]->date);
    }

    #[Test]
    public function it_respects_a_custom_limit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->createMentoringBooking(Carbon::tomorrow()->addDays($i)->setTime(10, 0));
        }

        $results = app(BookingRepository::class)->getUpcomingMentoringAndExamBookings(3);

        $this->assertCount(3, $results);
    }

    #[Test]
    public function it_resolves_callsign_from_the_linked_cts_booking(): void
    {
        $this->createMentoringBooking(Carbon::tomorrow()->setTime(10, 0), 'EGLL_APP');

        $results = app(BookingRepository::class)->getUpcomingMentoringAndExamBookings();

        $this->assertSame('EGLL_APP', $results[0]->position);
    }

    #[Test]
    public function it_shows_the_mentor_not_the_student_for_mentoring_sessions(): void
    {
        $data = $this->createMentoringBooking(Carbon::tomorrow()->setTime(10, 0));

        $results = app(BookingRepository::class)->getUpcomingMentoringAndExamBookings();

        $this->assertSame((string) $data['mentorAccount']->id, $results[0]->member['cid']);
        $this->assertSame($this->expectedDisplayName($data['mentorAccount']), $results[0]->member['display_name']);
    }

    #[Test]
    public function it_shows_the_examiner_not_the_student_for_exams(): void
    {
        $data = $this->createExamBooking(Carbon::tomorrow()->setTime(9, 0));

        $results = app(BookingRepository::class)->getUpcomingMentoringAndExamBookings();

        $this->assertSame((string) $data['examinerAccount']->id, $results[0]->member['cid']);
        $this->assertSame($this->expectedDisplayName($data['examinerAccount']), $results[0]->member['display_name']);
    }

    #[Test]
    public function it_only_sends_a_cid_and_an_abbreviated_name_to_the_frontend(): void
    {
        $this->createMentoringBooking(Carbon::tomorrow()->setTime(10, 0));

        $results = app(BookingRepository::class)->getUpcomingMentoringAndExamBookings();

        $this->assertSame(['cid', 'display_name'], array_keys($results[0]->member));
    }

    #[Test]
    public function it_falls_back_to_unknown_when_a_mentoring_session_has_no_mentor(): void
    {
        $startsAt = Carbon::tomorrow()->setTime(10, 0);
        $studentAccount = Account::factory()->create();
        $student = CtsMember::factory()->forAccount($studentAccount)->create();
        $endsAt = $startsAt->clone()->addHours(2);

        $session = Session::factory()->create([
            'student_id' => $student->id,
            'mentor_id' => null,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'taken_date' => $startsAt->toDateString(),
            'taken_from' => $startsAt->format('H:i:s'),
            'taken_to' => $endsAt->format('H:i:s'),
        ]);

        Booking::create([
            'position_id' => null,
            'member_id' => $studentAccount->id,
            'type' => Booking::TYPE_MENTORING,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'bookable_type' => Session::class,
            'bookable_id' => $session->id,
        ]);

        $results = app(BookingRepository::class)->getUpcomingMentoringAndExamBookings();

        $this->assertSame('Unknown', $results[0]->member['display_name'], 'Must not fall back to the student');
        $this->assertSame('', $results[0]->member['cid']);
    }

    #[Test]
    public function it_falls_back_to_unknown_when_an_exam_has_no_examiner(): void
    {
        $startsAt = Carbon::tomorrow()->setTime(9, 0);
        $studentAccount = Account::factory()->create();
        $student = CtsMember::factory()->forAccount($studentAccount)->create();
        $endsAt = $startsAt->clone()->addHours(2);

        // Accepted exam with no PracticalExaminers record (no leading examiner).
        $exam = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'position_1' => 'EGKK_TWR',
            'taken_date' => $startsAt->toDateString(),
            'taken_from' => $startsAt->format('H:i:s'),
            'taken_to' => $endsAt->format('H:i:s'),
        ]);

        Booking::create([
            'position_id' => null,
            'member_id' => $studentAccount->id,
            'type' => Booking::TYPE_EXAM,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'bookable_type' => ExamBooking::class,
            'bookable_id' => $exam->id,
        ]);

        $results = app(BookingRepository::class)->getUpcomingMentoringAndExamBookings();

        $this->assertSame('Unknown', $results[0]->member['display_name'], 'Must not fall back to the student');
        $this->assertSame('', $results[0]->member['cid']);
    }

    #[Test]
    public function it_includes_a_session_that_already_started_earlier_today(): void
    {
        $data = $this->createMentoringBooking(Carbon::today()->setTime(0, 30));

        $results = app(BookingRepository::class)->getUpcomingMentoringAndExamBookings();

        $this->assertTrue($results->contains(fn (object $b) => $b->id === (string) $data['booking']->id));
    }

    #[Test]
    public function it_shows_the_new_table_to_guests(): void
    {
        $this->createMentoringBooking(Carbon::tomorrow()->setTime(10, 0), 'EGLL_APP');

        Livewire::test(Calendar::class)
            ->assertSee('Upcoming mentoring')
            ->assertSee('EGLL_APP');
    }

    #[Test]
    public function it_shows_the_mentor_display_name_but_never_the_full_last_name(): void
    {
        $data = $this->createMentoringBooking(Carbon::tomorrow()->setTime(10, 0));

        // Pinned to a distinctive value: the factory's default random last name
        // (e.g. "Li", "Ho") could coincidentally appear elsewhere on the ~440-line
        // rendered page and make this assertion flaky.
        $data['mentorAccount']->update(['name_last' => 'Featherstonehaugh']);

        Livewire::test(Calendar::class)
            ->assertSee($this->expectedDisplayName($data['mentorAccount']), false)
            ->assertDontSee($data['mentorAccount']->name_last)
            ->assertDontSee($data['studentAccount']->name_last)
            ->assertDontSee((string) $data['studentAccount']->id);
    }

    #[Test]
    public function it_shows_an_empty_state_when_there_are_no_upcoming_sessions(): void
    {
        Livewire::test(Calendar::class)
            ->assertSee('No upcoming mentoring sessions or exams.');
    }
}
