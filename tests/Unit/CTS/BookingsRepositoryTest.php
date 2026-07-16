<?php

declare(strict_types=1);

namespace Tests\Unit\CTS;

use App\Models\Atc\Position;
use App\Models\Booking;
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

        $bookingTodayTwo = Booking::factory()->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_MENTORING,
            'starts_at' => Carbon::parse($this->today.' 18:00:00'),
            'ends_at' => Carbon::parse($this->today.' 20:00:00'),
        ]);

        $bookings = $this->subjectUnderTest->getTodaysBookings();

        $this->assertInstanceOf(Collection::class, $bookings);
        $this->assertCount(2, $bookings);

        $this->assertEquals([
            'id' => (string) $bookingTodayOne->id,
            'date' => $this->today,
            'from' => '17:00',
            'to' => '19:00',
            'position' => 'EGKK_APP',
            'type' => 'BK',
            'member' => [
                'id' => (string) $member->id,
                'name' => $member->name,
            ],
        ], (array) $bookings->get(0));
        $this->assertEquals([
            'id' => (string) $bookingTodayTwo->id,
            'date' => $this->today,
            'from' => '18:00',
            'to' => '20:00',
            'position' => 'EGKK_APP',
            'type' => 'ME',
            'member' => [
                'id' => (string) $member->id,
                'name' => $member->name,
            ],
        ], (array) $bookings->get(1));
    }

    #[Test]
    public function it_hides_member_details_on_exam_booking(): void
    {
        $member = Account::factory()->create();
        $position = Position::factory()->create();

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
        ]);

        $bookings = $this->subjectUnderTest->getTodaysBookings();

        $this->assertEquals([
            'id' => (string) $member->id,
            'name' => $member->name,
        ], $bookings->get(0)->member);

        $this->assertEquals([
            'id' => '',
            'name' => 'Hidden',
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
}
