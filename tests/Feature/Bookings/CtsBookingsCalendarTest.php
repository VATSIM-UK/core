<?php

declare(strict_types=1);

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\Calendar;
use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Cts\Event;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member as CtsMember;
use App\Models\Cts\PracticalExaminers;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Repositories\Cts\BookingRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CtsBookingsCalendarTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_shows_cts_only_bookings_live(): void
    {
        $date = Carbon::parse('2026-08-01');
        $member = Account::factory()->create();
        // CTS members have an internal `id` distinct from the VATSIM CID (see MemberFactory::forAccount).
        $ctsMember = CtsMember::factory()->forAccount($member)->create();

        $cts = CtsBooking::factory()->create([
            // Deliberately absent from the core `positions` table.
            'position' => 'EGXX_FSS',
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'date' => $date->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $match = $bookings->firstWhere('cts_booking_id', (int) $cts->id);
        $this->assertNotNull($match, 'CTS-only booking should appear');
        $this->assertEquals('cts', $match->source);
        $this->assertEquals('10:00', $match->from);
        $this->assertEquals('12:00', $match->to);
        $this->assertSame('EGXX_FSS', $match->position);
        $this->assertNull($match->position_id);
        $this->assertSame((string) $member->id, $match->member['cid']);
        $this->assertSame($member->name, $match->member['name']);
    }

    #[Test]
    public function it_resolves_cts_members_by_cid_not_the_cts_internal_id(): void
    {
        $date = Carbon::parse('2026-08-01');
        $member = Account::factory()->create();
        $ctsMember = CtsMember::factory()->forAccount($member)->create();
        $this->assertNotSame($member->id, $ctsMember->id, 'Test relies on CTS member id differing from the CID');

        $cts = CtsBooking::factory()->create([
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'date' => $date->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $match = $bookings->firstWhere('cts_booking_id', (int) $cts->id);
        $this->assertSame((string) $member->id, $match->member['cid']);
        $this->assertSame($member->name_first.' '.mb_substr($member->name_last, 0, 1).'.', $match->member['display_name']);
    }

    #[Test]
    public function it_matches_cts_positions_that_exist_in_the_positions_table(): void
    {
        $date = Carbon::parse('2026-08-01');
        $member = Account::factory()->create();
        $ctsMember = CtsMember::factory()->forAccount($member)->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        $cts = CtsBooking::factory()->create([
            'position' => 'EGKK_APP',
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'date' => $date->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $match = $bookings->firstWhere('cts_booking_id', (int) $cts->id);
        $this->assertNotNull($match, 'CTS-only booking should appear');
        $this->assertSame('EGKK_APP', $match->position);
        $this->assertSame($position->id, $match->position_id);
    }

    #[Test]
    public function it_falls_back_to_the_raw_position_when_there_is_no_core_match(): void
    {
        $date = Carbon::parse('2026-08-01');
        $member = Account::factory()->create();
        $ctsMember = CtsMember::factory()->forAccount($member)->create();

        // A different callsign exists in core, proving the CTS position is not forced
        // through the core positions table when no exact match exists.
        Position::factory()->create(['callsign' => 'EGKK_APP']);

        $cts = CtsBooking::factory()->create([
            'position' => 'EGXX_FSS',
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'date' => $date->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $match = $bookings->firstWhere('cts_booking_id', (int) $cts->id);
        $this->assertNotNull($match, 'CTS-only booking should appear');
        $this->assertSame('EGXX_FSS', $match->position);
        $this->assertNull($match->position_id);
    }

    #[Test]
    public function it_does_not_duplicate_imported_cts_bookings(): void
    {
        $date = Carbon::parse('2026-08-01');
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        $cts = CtsBooking::factory()->create([
            'position' => 'EGKK_APP',
            'member_id' => $member->id,
            'type' => 'BK',
            'date' => $date->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        // Simulate an already-imported mirror row in core.
        Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => $date->toDateString().' 10:00:00',
            'ends_at' => $date->toDateString().' 12:00:00',
            'cts_booking_id' => $cts->id,
            'bookable_type' => CtsBooking::class,
            'bookable_id' => $cts->id,
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $matches = $bookings->where('cts_booking_id', (int) $cts->id);
        $this->assertCount(1, $matches, 'Imported CTS booking must appear exactly once');
        $this->assertEquals('core', $matches->first()->source);
    }

    #[Test]
    public function owner_can_cancel_a_cts_only_standard_booking(): void
    {
        $member = Account::factory()->create();
        $cts = CtsBooking::factory()->create([
            'member_id' => $member->id,
            'type' => 'BK',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('cancelBooking', ['cts_booking_id' => (int) $cts->id])
            ->assertDispatched('booking-deleted');

        $this->assertDatabaseMissing('bookings', ['id' => $cts->id], 'cts');
    }

    #[Test]
    public function cancelling_a_mirrored_cts_booking_removes_both_rows(): void
    {
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);
        $cts = CtsBooking::factory()->create([
            'position' => 'EGKK_APP',
            'member_id' => $member->id,
            'type' => 'BK',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);
        $core = Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
            'cts_booking_id' => $cts->id,
            'bookable_type' => CtsBooking::class,
            'bookable_id' => $cts->id,
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('cancelBooking', ['id' => (string) $core->id])
            ->assertDispatched('booking-deleted');

        $this->assertDatabaseMissing('bookings', ['id' => $core->id]);
        $this->assertDatabaseMissing('bookings', ['id' => $cts->id], 'cts');
    }

    #[Test]
    public function cancelling_a_mirrored_booking_by_cts_id_also_removes_the_core_mirror(): void
    {
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);
        $cts = CtsBooking::factory()->create([
            'position' => 'EGKK_APP',
            'member_id' => $member->id,
            'type' => 'BK',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);
        $core = Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
            'cts_booking_id' => $cts->id,
            'bookable_type' => CtsBooking::class,
            'bookable_id' => $cts->id,
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('cancelBooking', ['cts_booking_id' => (int) $cts->id])
            ->assertDispatched('booking-deleted');

        $this->assertDatabaseMissing('bookings', ['id' => $core->id]);
        $this->assertDatabaseMissing('bookings', ['id' => $cts->id], 'cts');
    }

    #[Test]
    public function it_rejects_cancelling_someone_elses_cts_booking(): void
    {
        $owner = Account::factory()->create();
        $other = Account::factory()->create();
        $cts = CtsBooking::factory()->create([
            'member_id' => $owner->id,
            'type' => 'BK',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Livewire::actingAs($other)
            ->test(Calendar::class)
            ->call('cancelBooking', ['cts_booking_id' => (int) $cts->id])
            ->assertDispatched('booking-error');

        $this->assertDatabaseHas('bookings', ['id' => $cts->id], 'cts');
    }

    #[Test]
    public function it_rejects_cancelling_a_non_standard_cts_booking(): void
    {
        $member = Account::factory()->create();
        $cts = CtsBooking::factory()->create([
            'member_id' => $member->id,
            'type' => 'EX',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('cancelBooking', ['cts_booking_id' => (int) $cts->id])
            ->assertDispatched('booking-error');

        $this->assertDatabaseHas('bookings', ['id' => $cts->id], 'cts');
    }

    #[Test]
    public function creating_a_booking_that_overlaps_a_live_cts_booking_is_rejected(): void
    {
        $member = Account::factory()->withQualification()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 5]);
        $member->qualifications()->sync([$qual->id]);
        $member = $member->fresh();

        $position = Position::factory()->create(['type' => Position::TYPE_ENROUTE, 'callsign' => 'LON_CTR']);

        // CTS-only booking (not imported into core) on the same position/time.
        CtsBooking::factory()->create([
            'position' => 'LON_CTR',
            'member_id' => Account::factory()->create()->id,
            'type' => 'BK',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('createBooking', [
                'starts_at' => Carbon::tomorrow()->setHour(11)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::tomorrow()->setHour(13)->format('Y-m-d H:i:s'),
                'position_id' => (string) $position->id,
            ])
            ->assertDispatched('booking-warning');

        $this->assertDatabaseMissing('bookings', ['member_id' => $member->id]);
    }

    #[Test]
    public function it_shows_member_details_for_cts_only_standard_bookings(): void
    {
        $date = Carbon::parse('2026-08-01');
        $member = Account::factory()->create();
        $ctsMember = CtsMember::factory()->forAccount($member)->create();

        $cts = CtsBooking::factory()->create([
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'date' => $date->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $match = $bookings->firstWhere('cts_booking_id', (int) $cts->id);
        $this->assertNotNull($match);
        $this->assertSame((string) $member->id, $match->member['cid']);
        $this->assertSame($member->name, $match->member['name']);
    }

    #[Test]
    public function it_shows_unknown_member_when_cts_member_has_no_core_account(): void
    {
        $date = Carbon::parse('2026-08-01');
        // CTS member whose cid does not exist in core mship_account.
        $ctsMember = CtsMember::factory()->create(['cid' => 9999999]);

        $cts = CtsBooking::factory()->create([
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'date' => $date->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $match = $bookings->firstWhere('cts_booking_id', (int) $cts->id);
        $this->assertNotNull($match);
        $this->assertSame('Unknown', $match->member['display_name']);
        $this->assertSame('Unknown', $match->member['name']);
        $this->assertSame('', $match->member['cid']);
    }

    #[Test]
    public function it_detects_overlap_when_cts_booking_has_a_position_not_in_core_table(): void
    {
        $member = Account::factory()->withQualification()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 5]);
        $member->qualifications()->sync([$qual->id]);
        $member = $member->fresh();

        // Position in core table
        $position = Position::factory()->create(['type' => Position::TYPE_ENROUTE, 'callsign' => 'LON_SC_CTR']);

        // CTS booking with raw position — matches the core position callsign
        CtsBooking::factory()->create([
            'position' => 'LON_SC_CTR',
            'member_id' => Account::factory()->create()->id,
            'type' => 'BK',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('createBooking', [
                'starts_at' => Carbon::tomorrow()->setHour(11)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::tomorrow()->setHour(13)->format('Y-m-d H:i:s'),
                'position_id' => (string) $position->id,
            ])
            ->assertDispatched('booking-warning');

        $this->assertDatabaseMissing('bookings', ['member_id' => $member->id]);
    }

    #[Test]
    public function it_shows_the_leading_examiner_instead_of_the_student_for_exam_bookings(): void
    {
        $date = Carbon::parse('2026-08-01');
        $studentAccount = Account::factory()->create();
        $examinerAccount = Account::factory()->create();
        $student = CtsMember::factory()->forAccount($studentAccount)->create();
        $examiner = CtsMember::factory()->forAccount($examinerAccount)->create();

        $exam = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'position_1' => 'EGKK_TWR',
            'taken_date' => $date->toDateString(),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        PracticalExaminers::create([
            'examid' => $exam->id,
            'senior' => $examiner->id,
            'other' => null,
            'trainee' => null,
        ]);

        Booking::create([
            'position_id' => null,
            'member_id' => $studentAccount->id,
            'type' => Booking::TYPE_EXAM,
            'starts_at' => $date->toDateString().' 10:00:00',
            'ends_at' => $date->toDateString().' 12:00:00',
            'bookable_type' => ExamBooking::class,
            'bookable_id' => $exam->id,
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $this->assertCount(1, $bookings);
        $this->assertSame((string) $examinerAccount->id, $bookings->first()->member['cid'], 'Exam booking must show the leading examiner, not the student');
        $this->assertSame($examinerAccount->name, $bookings->first()->member['name']);
    }

    #[Test]
    public function it_shows_the_mentor_instead_of_the_student_for_mentoring_bookings(): void
    {
        $date = Carbon::parse('2026-08-01');
        $studentAccount = Account::factory()->create();
        $mentorAccount = Account::factory()->create();
        $student = CtsMember::factory()->forAccount($studentAccount)->create();
        $mentor = CtsMember::factory()->forAccount($mentorAccount)->create();

        $session = Session::factory()->create([
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'taken_date' => $date->toDateString(),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        Booking::create([
            'position_id' => null,
            'member_id' => $studentAccount->id,
            'type' => Booking::TYPE_MENTORING,
            'starts_at' => $date->toDateString().' 10:00:00',
            'ends_at' => $date->toDateString().' 12:00:00',
            'bookable_type' => Session::class,
            'bookable_id' => $session->id,
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $this->assertCount(1, $bookings);
        $this->assertSame((string) $mentorAccount->id, $bookings->first()->member['cid'], 'Mentoring booking must show the mentor, not the student');
        $this->assertSame($mentorAccount->name, $bookings->first()->member['name']);
    }

    #[Test]
    public function it_shows_the_leading_examiner_for_cts_only_exam_bookings(): void
    {
        $date = Carbon::parse('2026-08-01');
        $studentAccount = Account::factory()->create();
        $examinerAccount = Account::factory()->create();
        $student = CtsMember::factory()->forAccount($studentAccount)->create();
        $examiner = CtsMember::factory()->forAccount($examinerAccount)->create();

        $exam = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'position_1' => 'EGKK_TWR',
            'taken_date' => $date->toDateString(),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
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
            'position' => 'EGKK_TWR',
            'date' => $date->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $match = $bookings->firstWhere('cts_booking_id', (int) $cts->id);
        $this->assertNotNull($match);
        $this->assertSame((string) $examinerAccount->id, $match->member['cid'], 'CTS-only exam booking must show the leading examiner, not the student');
    }

    #[Test]
    public function it_shows_the_mentor_for_cts_only_mentoring_bookings(): void
    {
        $date = Carbon::parse('2026-08-01');
        $studentAccount = Account::factory()->create();
        $mentorAccount = Account::factory()->create();
        $student = CtsMember::factory()->forAccount($studentAccount)->create();
        $mentor = CtsMember::factory()->forAccount($mentorAccount)->create();

        Session::factory()->create([
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'taken_date' => $date->toDateString(),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        $cts = CtsBooking::factory()->create([
            'type' => 'ME',
            'member_id' => $student->id,
            'position' => 'EGLL_APP',
            'date' => $date->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $match = $bookings->firstWhere('cts_booking_id', (int) $cts->id);
        $this->assertNotNull($match);
        $this->assertSame((string) $mentorAccount->id, $match->member['cid'], 'CTS-only mentoring booking must show the mentor, not the student');
    }

    #[Test]
    public function it_never_falls_back_to_the_student_when_an_exam_has_no_examiner(): void
    {
        $date = Carbon::parse('2026-08-01');
        $studentAccount = Account::factory()->create();
        $student = CtsMember::factory()->forAccount($studentAccount)->create();

        // Accepted exam with no PracticalExaminers record (no leading examiner).
        $exam = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'position_1' => 'EGKK_TWR',
            'taken_date' => $date->toDateString(),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        Booking::create([
            'position_id' => null,
            'member_id' => $studentAccount->id,
            'type' => Booking::TYPE_EXAM,
            'starts_at' => $date->toDateString().' 10:00:00',
            'ends_at' => $date->toDateString().' 12:00:00',
            'bookable_type' => ExamBooking::class,
            'bookable_id' => $exam->id,
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $this->assertSame('Unknown', $bookings->first()->member['name'], 'Exam booking must not fall back to the student');
        $this->assertSame('', $bookings->first()->member['cid']);
    }

    #[Test]
    public function it_never_falls_back_to_the_student_when_a_mentoring_booking_has_no_mentor(): void
    {
        $date = Carbon::parse('2026-08-01');
        $studentAccount = Account::factory()->create();
        $student = CtsMember::factory()->forAccount($studentAccount)->create();

        // Accepted session with no mentor assigned.
        $session = Session::factory()->create([
            'student_id' => $student->id,
            'mentor_id' => null,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'taken_date' => $date->toDateString(),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        Booking::create([
            'position_id' => null,
            'member_id' => $studentAccount->id,
            'type' => Booking::TYPE_MENTORING,
            'starts_at' => $date->toDateString().' 10:00:00',
            'ends_at' => $date->toDateString().' 12:00:00',
            'bookable_type' => Session::class,
            'bookable_id' => $session->id,
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $this->assertSame('Unknown', $bookings->first()->member['name'], 'Mentoring booking must not fall back to the student');
        $this->assertSame('', $bookings->first()->member['cid']);
    }

    #[Test]
    public function it_shows_events_from_the_cts_events_table(): void
    {
        $date = Carbon::parse('2026-08-01');
        $member = Account::factory()->create();
        $addBy = CtsMember::factory()->forAccount($member)->create();

        Event::factory()->create([
            'event' => 'Cross the Pond',
            'date' => $date->toDateString(),
            'from' => '18:00:00',
            'to' => '22:00:00',
            'add_by' => $addBy->id,
            'gone' => 0,
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $event = $bookings->firstWhere('type', 'EV');
        $this->assertNotNull($event, 'Events from the CTS events table must appear on the calendar');
        $this->assertSame('event', $event->source);
        $this->assertSame('18:00', $event->from);
        $this->assertSame('22:00', $event->to);
        $this->assertSame('Cross the Pond', $event->event_name, 'The event name must be carried through for display');
        $this->assertSame('Unknown', $event->member['name'], 'The event author must not be shown');
        $this->assertSame('', $event->member['cid']);
    }

    #[Test]
    public function it_carries_the_event_name_through_to_the_timeline(): void
    {
        $date = Carbon::today();

        Event::factory()->create([
            'event' => 'Cross the Pond',
            'date' => $date->toDateString(),
            'from' => '18:00:00',
            'to' => '22:00:00',
            'gone' => 0,
        ]);

        $events = Livewire::test(Calendar::class)->get('events');

        $this->assertCount(1, $events);
        $this->assertSame(
            'Cross the Pond',
            $events[0]['event_name'],
            'The events row renders event_name, so buildTimeline must not drop it'
        );
    }

    #[Test]
    public function it_stacks_overlapping_events_into_separate_lanes(): void
    {
        $date = Carbon::today();

        Event::factory()->create([
            'event' => 'Early Start',
            'date' => $date->toDateString(),
            'from' => '18:00:00',
            'to' => '21:00:00',
            'gone' => 0,
        ]);
        Event::factory()->create([
            'event' => 'Late Finish',
            'date' => $date->toDateString(),
            'from' => '20:00:00',
            'to' => '23:00:00',
            'gone' => 0,
        ]);

        $component = Livewire::test(Calendar::class);

        $this->assertSame(2, $component->get('eventLaneCount'), 'The events row must grow to fit both events');
        $this->assertSame([0, 1], array_column($component->get('events'), 'lane'));
    }

    #[Test]
    public function it_keeps_the_events_row_at_one_lane_when_events_do_not_overlap(): void
    {
        $date = Carbon::today();

        Event::factory()->create([
            'event' => 'Afternoon',
            'date' => $date->toDateString(),
            'from' => '14:00:00',
            'to' => '16:00:00',
            'gone' => 0,
        ]);
        Event::factory()->create([
            'event' => 'Evening',
            'date' => $date->toDateString(),
            'from' => '18:00:00',
            'to' => '21:00:00',
            'gone' => 0,
        ]);

        $this->assertSame(1, Livewire::test(Calendar::class)->get('eventLaneCount'));
    }

    #[Test]
    public function it_hides_events_when_a_callsign_filter_is_applied(): void
    {
        $date = Carbon::today();

        Event::factory()->create([
            'event' => 'Unknown Pleasures',
            'date' => $date->toDateString(),
            'from' => '18:00:00',
            'to' => '22:00:00',
            'gone' => 0,
        ]);

        $component = Livewire::test(Calendar::class);
        $this->assertCount(1, $component->get('events'));

        // "UN" used to match the "Unknown" callsign placeholder events are given.
        $component->set('positionFilter', 'UN');

        $this->assertSame([], $component->get('events'), 'Events have no callsign to match against');
    }

    #[Test]
    public function it_excludes_events_marked_as_gone(): void
    {
        $date = Carbon::parse('2026-08-01');

        Event::factory()->create([
            'date' => $date->toDateString(),
            'from' => '18:00:00',
            'to' => '22:00:00',
            'gone' => 1,
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $this->assertEmpty($bookings->where('type', 'EV'), 'Gone events must not appear on the calendar');
    }
}
