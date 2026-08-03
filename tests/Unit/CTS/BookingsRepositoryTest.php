<?php

declare(strict_types=1);

namespace Tests\Unit\CTS;

use App\Models\Atc\Position;
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
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookingsRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    protected BookingRepository $subjectUnderTest;

    protected string $today;

    protected string $tomorrow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subjectUnderTest = resolve(BookingRepository::class);
        $this->today = $this->knownDate->toDateString();
        $this->tomorrow = $this->knownDate->copy()->addDay()->toDateString();
    }

    #[Test]
    public function it_can_return_a_list_of_bookings_for_today(): void
    {
        Booking::factory()->count(10)->create([
            'starts_at' => $this->knownDate->copy()->setHour(10),
            'ends_at' => $this->knownDate->copy()->setHour(12),
        ]);

        $bookings = $this->subjectUnderTest->getBookings(Carbon::parse($this->today));

        $this->assertInstanceOf(Collection::class, $bookings);
        $this->assertCount(10, $bookings);
    }

    #[Test]
    public function it_can_return_a_list_of_todays_bookings_with_owner_and_type(): void
    {
        Booking::factory()->count(2)->create([
            'starts_at' => $this->knownDate->copy()->addDays(5)->setHour(10),
            'ends_at' => $this->knownDate->copy()->addDays(5)->setHour(12),
        ]);

        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);
        $member = Account::factory()->create();
        $bookingTodayOne = Booking::factory()->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::parse($this->today.' 17:00:00'),
            'ends_at' => Carbon::parse($this->today.' 19:00:00'),
        ]);

        $mentorAccount = Account::factory()->create();
        $mentor = CtsMember::factory()->forAccount($mentorAccount)->create();
        $session = Session::factory()->create([
            'student_id' => CtsMember::factory()->create()->id,
            'mentor_id' => $mentor->id,
            'position' => 'EGKK_APP',
            'taken' => 1,
            'taken_date' => $this->today,
            'taken_from' => '18:00:00',
            'taken_to' => '20:00:00',
        ]);

        $bookingTodayTwo = Booking::factory()->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_MENTORING,
            'starts_at' => Carbon::parse($this->today.' 18:00:00'),
            'ends_at' => Carbon::parse($this->today.' 20:00:00'),
            'bookable_type' => Session::class,
            'bookable_id' => $session->id,
        ]);

        $bookings = $this->subjectUnderTest->getTodaysBookings();

        $this->assertInstanceOf(Collection::class, $bookings);
        $this->assertCount(2, $bookings);

        $this->assertEquals([
            'id' => (string) $bookingTodayOne->id,
            'source' => 'core',
            'cts_booking_id' => null,
            'position_id' => $bookingTodayOne->position_id,
            'date' => $this->today,
            'from' => '17:00',
            'to' => '19:00',
            'position' => 'EGKK_APP',
            'type' => 'BK',
            'member' => [
                'id' => (string) $member->id,
                'cid' => (string) $member->id,
                'name' => $member->name,
                'display_name' => $member->name_first.' '.mb_substr($member->name_last, 0, 1).'.',
            ],
        ], (array) $bookings->get(0));
        $this->assertEquals([
            'id' => (string) $bookingTodayTwo->id,
            'source' => 'core',
            'cts_booking_id' => null,
            'position_id' => $bookingTodayTwo->position_id,
            'date' => $this->today,
            'from' => '18:00',
            'to' => '20:00',
            'position' => 'EGKK_APP',
            'type' => 'ME',
            'member' => [
                'id' => (string) $mentorAccount->id,
                'cid' => (string) $mentorAccount->id,
                'name' => $mentorAccount->name,
                'display_name' => $mentorAccount->name_first.' '.mb_substr($mentorAccount->name_last, 0, 1).'.',
            ],
        ], (array) $bookings->get(1));
    }

    #[Test]
    public function it_shows_the_examiner_on_exam_bookings(): void
    {
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        $examinerAccount = Account::factory()->create();
        $examiner = CtsMember::factory()->forAccount($examinerAccount)->create();

        $exam = ExamBooking::factory()->create([
            'student_id' => CtsMember::factory()->create()->id,
            'position_1' => $position->callsign,
            'taken' => 1,
            'taken_date' => $this->knownDate->format('Y-m-d'),
            'taken_from' => '18:00:00',
            'taken_to' => '20:00:00',
        ]);

        PracticalExaminers::create([
            'examid' => $exam->id,
            'senior' => $examiner->id,
            'other' => null,
            'trainee' => null,
        ]);

        Booking::factory()->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => $this->knownDate->copy()->setHour(17),
            'ends_at' => $this->knownDate->copy()->setHour(19),
        ]);
        Booking::factory()->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_EXAM,
            'starts_at' => $this->knownDate->copy()->setHour(18),
            'ends_at' => $this->knownDate->copy()->setHour(20),
            'bookable_type' => ExamBooking::class,
            'bookable_id' => $exam->id,
        ]);

        $bookings = $this->subjectUnderTest->getTodaysBookings();

        $this->assertEquals([
            'id' => (string) $member->id,
            'cid' => (string) $member->id,
            'name' => $member->name,
            'display_name' => $member->name_first.' '.mb_substr($member->name_last, 0, 1).'.',
        ], $bookings->get(0)->member);

        $this->assertEquals([
            'id' => (string) $examinerAccount->id,
            'cid' => (string) $examinerAccount->id,
            'name' => $examinerAccount->name,
            'display_name' => $examinerAccount->name_first.' '.mb_substr($examinerAccount->name_last, 0, 1).'.',
        ], $bookings->get(1)->member);
    }

    #[Test]
    public function it_can_return_a_list_of_todays_live_atc_bookings(): void
    {
        $atcPosition = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);
        $nonAtcPosition = Position::factory()->create(['callsign' => 'EGXX_ATIS', 'type' => Position::TYPE_ATIS]);

        Booking::factory()->create([
            'position_id' => $atcPosition->id,
            'starts_at' => $this->knownDate->copy()->setHour(10),
            'ends_at' => $this->knownDate->copy()->setHour(12),
        ]);
        Booking::factory()->create([
            'position_id' => $nonAtcPosition->id,
            'starts_at' => $this->knownDate->copy()->setHour(10),
            'ends_at' => $this->knownDate->copy()->setHour(12),
        ]);
        Booking::factory()->create([
            'position_id' => $atcPosition->id,
            'starts_at' => $this->knownDate->copy()->addDay()->setHour(10),
            'ends_at' => $this->knownDate->copy()->addDay()->setHour(12),
        ]);

        $atcBookings = $this->subjectUnderTest->getTodaysLiveAtcBookings();

        $this->assertInstanceOf(Collection::class, $atcBookings);
        $this->assertCount(1, $atcBookings);
    }

    #[Test]
    public function it_can_return_a_booking_without_a_known_member(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::factory()->create([
            'position_id' => $position->id,
            'member_id' => null,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => $this->knownDate->copy()->setHour(10),
            'ends_at' => $this->knownDate->copy()->setHour(12),
        ]);

        $this->subjectUnderTest->getTodaysLiveAtcBookings();

        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function it_returns_bookings_in_start_time_order(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);
        $member = Account::factory()->create();

        $afternoon = Booking::factory()->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => $this->knownDate->copy()->setHour(16),
            'ends_at' => $this->knownDate->copy()->setHour(17),
        ]);
        $morning = Booking::factory()->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => $this->knownDate->copy()->setHour(9),
            'ends_at' => $this->knownDate->copy()->setHour(11),
        ]);
        $night = Booking::factory()->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => $this->knownDate->copy()->setHour(22),
            'ends_at' => $this->knownDate->copy()->setHour(23),
        ]);

        $todaysBookings = $this->subjectUnderTest->getTodaysBookings();
        $todaysAtcBookings = $this->subjectUnderTest->getTodaysLiveAtcBookings();

        $this->assertEquals($todaysBookings->toArray(), $todaysAtcBookings->toArray());
        $this->assertEquals($morning->id, (int) $todaysBookings->get(0)->id);
        $this->assertEquals($afternoon->id, (int) $todaysBookings->get(1)->id);
        $this->assertEquals($night->id, (int) $todaysBookings->get(2)->id);
    }

    #[Test]
    public function it_merges_core_and_cts_bookings_ordered_by_start_time(): void
    {
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        // Core booking at 11:00
        Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::parse($this->today.' 11:00:00'),
            'ends_at' => Carbon::parse($this->today.' 13:00:00'),
        ]);

        // CTS booking at 09:00 — earlier than the core booking
        $ctsMember = CtsMember::factory()->forAccount($member)->create();
        CtsBooking::factory()->create([
            'position' => 'EGKK_APP',
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'date' => $this->today,
            'from' => '09:00:00',
            'to' => '10:30:00',
        ]);

        $bookings = $this->subjectUnderTest->getBookings(Carbon::parse($this->today));

        $this->assertCount(2, $bookings, 'Must return both core and CTS bookings');
        $this->assertEquals('09:00', $bookings->get(0)->from, 'CTS booking at 09:00 must come first');
        $this->assertEquals('11:00', $bookings->get(1)->from, 'Core booking at 11:00 must come second');
    }

    #[Test]
    public function it_renders_the_cts_callsign_for_core_bookings_without_a_core_position(): void
    {
        $member = Account::factory()->create();
        $ctsMember = CtsMember::factory()->forAccount($member)->create();

        // CTS booking on a training position NOT present in the core positions table.
        $cts = CtsBooking::factory()->create([
            'position' => 'EGSS_APP',
            'member_id' => $ctsMember->id,
            'type' => 'EX',
            'date' => $this->today,
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        // Core mirror with no position_id (training position is not in core).
        Booking::create([
            'position_id' => null,
            'member_id' => $member->id,
            'type' => Booking::TYPE_EXAM,
            'starts_at' => Carbon::parse($this->today.' 10:00:00'),
            'ends_at' => Carbon::parse($this->today.' 12:00:00'),
            'cts_booking_id' => $cts->id,
            'bookable_type' => CtsBooking::class,
            'bookable_id' => $cts->id,
        ]);

        $bookings = $this->subjectUnderTest->getBookings(Carbon::parse($this->today));

        $this->assertCount(1, $bookings, 'Core mirror and CTS row must deduplicate to one booking');
        $this->assertSame('EGSS_APP', $bookings->first()->position, 'Must render the CTS callsign (cts-first)');
    }

    #[Test]
    public function it_prefers_the_cts_callsign_over_the_core_position_callsign(): void
    {
        $member = Account::factory()->create();
        $ctsMember = CtsMember::factory()->forAccount($member)->create();

        // A core position exists, but the CTS booking is authoritative for the callsign,
        // so a divergent CTS callsign must win over the core position's callsign.
        $position = Position::factory()->create(['callsign' => 'EGLL_APP', 'type' => Position::TYPE_APPROACH]);

        $cts = CtsBooking::factory()->create([
            'position' => 'EGSS_APP',
            'member_id' => $ctsMember->id,
            'type' => 'EX',
            'date' => $this->today,
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_EXAM,
            'starts_at' => Carbon::parse($this->today.' 10:00:00'),
            'ends_at' => Carbon::parse($this->today.' 12:00:00'),
            'cts_booking_id' => $cts->id,
            'bookable_type' => CtsBooking::class,
            'bookable_id' => $cts->id,
        ]);

        $bookings = $this->subjectUnderTest->getBookings(Carbon::parse($this->today));

        $this->assertCount(1, $bookings);
        $this->assertSame('EGSS_APP', $bookings->first()->position, 'The CTS callsign must win over the core position callsign');
        $this->assertSame($position->id, $bookings->first()->position_id);
    }
}
