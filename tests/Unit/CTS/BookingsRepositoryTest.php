<?php

namespace Tests\Unit\CTS;

use App\Models\Cts\Booking;
use App\Models\Cts\Member;
use App\Repositories\Cts\BookingRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookingsRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @var BookingRepository */
    protected $subjectUnderTest;

    /** @var string */
    protected $today;

    /** @var string */
    protected $tomorrow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subjectUnderTest = resolve(BookingRepository::class);
        $this->today = $this->knownDate->toDateString();
        $this->tomorrow = $this->knownDate->copy()->addDay()->toDateString();
    }

    #[Test]
    public function it_can_return_a_list_of_bookings_for_today()
    {
        // Ensure created items are on the same date the repo will query
        Booking::factory()->count(10)->create([
            'date' => $this->today,
            'from' => '10:00',
            'to' => '11:00',
            'type' => 'BK',
        ]);

        $bookings = $this->subjectUnderTest->getBookings(Carbon::parse($this->today));

        // Repo returns a Collection...
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $bookings);
        $this->assertCount(10, $bookings);

        // ...of Eloquent Booking models (mutated/formatted by formatBookings)
        $this->assertInstanceOf(Booking::class, $bookings->first());
    }

    #[Test]
    public function it_can_return_a_list_of_todays_bookings_with_owner_and_type()
    {
        // Noise on a different date
        \App\Models\Cts\Booking::factory()->count(2)->create([
            'date' => $this->knownDate->copy()->addDays(5)->toDateString(),
            'from' => '10:00',
            'to' => '11:00',
            'type' => 'BK',
        ]);

        $bookingTodayOne = \App\Models\Cts\Booking::factory()->create([
            'id' => '96155',
            'date' => $this->today,
            'from' => '17:00',
            'to' => '18:00',
            'member_id' => \App\Models\Cts\Member::factory()->create()->id,
            'type' => 'BK',
        ]);

        $bookingTodayTwo = \App\Models\Cts\Booking::factory()->create([
            'id' => '96156',
            'date' => $this->today,
            'from' => '18:00',
            'to' => '19:00',
            'member_id' => \App\Models\Cts\Member::factory()->create()->id,
            'type' => 'ME',
        ]);

        $bookings = $this->subjectUnderTest->getTodaysBookings();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $bookings);
        $this->assertCount(2, $bookings);

        // Build expected minimal payloads
        $expected0 = [
            'id' => $bookingTodayOne->id,
            'date' => $this->today, // repo formats to Y-m-d
            'from' => \Carbon\Carbon::parse($bookingTodayOne->from)->format('H:i'),
            'to' => \Carbon\Carbon::parse($bookingTodayOne->to)->format('H:i'),
            'position' => $bookingTodayOne->position,
            'type' => $bookingTodayOne->type,
            'member' => [
                'id' => $bookingTodayOne->member->cid,
                'name' => $bookingTodayOne->member->name,
            ],
        ];

        $expected1 = [
            'id' => $bookingTodayTwo->id,
            'date' => $this->today,
            'from' => \Carbon\Carbon::parse($bookingTodayTwo->from)->format('H:i'),
            'to' => \Carbon\Carbon::parse($bookingTodayTwo->to)->format('H:i'),
            'position' => $bookingTodayTwo->position,
            'type' => $bookingTodayTwo->type,
            'member' => [
                'id' => $bookingTodayTwo->member->cid,
                'name' => $bookingTodayTwo->member->name,
            ],
        ];

        // Only compare the keys we care about (Eloquent adds many others)
        $actual0 = collect($bookings->get(0)->toArray())->only(array_keys($expected0))->toArray();
        $actual1 = collect($bookings->get(1)->toArray())->only(array_keys($expected1))->toArray();

        // For nested 'member', also narrow to the expected keys
        $actual0['member'] = collect($actual0['member'])->only(['id', 'name'])->toArray();
        $actual1['member'] = collect($actual1['member'])->only(['id', 'name'])->toArray();

        $this->assertEquals($expected0, $actual0);
        $this->assertEquals($expected1, $actual1);
    }

    #[Test]
    public function it_hides_member_details_on_exam_booking()
    {
        $normalBooking = Booking::factory()->create([
            'date' => $this->today,
            'from' => '17:00',
            'to' => '18:00',
            'type' => 'BK',
        ]);

        Booking::factory()->create([
            'date' => $this->today,
            'from' => '18:00',
            'to' => '19:00',
            'type' => 'EX',
        ]);

        $bookings = $this->subjectUnderTest->getTodaysBookings();

        $this->assertEquals([
            'id' => $normalBooking->member->cid,
            'name' => $normalBooking->member->name,
        ], $bookings->get(0)['member']);

        $this->assertEquals([
            'id' => '',
            'name' => 'Hidden',
        ], $bookings->get(1)['member']);
    }

    #[Test]
    public function it_can_return_a_list_of_todays_live_atc_bookings()
    {
        Booking::factory()->create(['date' => $this->today, 'position' => 'EGKK_APP',  'from' => '10:00', 'to' => '11:00']); // live ATC
        Booking::factory()->create(['date' => $this->today, 'position' => 'EGKK_SBAT', 'from' => '12:00', 'to' => '13:00']); // sweatbox
        Booking::factory()->create(['date' => $this->today, 'position' => 'P1_VATSIM', 'from' => '14:00', 'to' => '15:00']); // pilot
        Booking::factory()->create(['date' => $this->tomorrow, 'position' => 'EGKK_APP', 'from' => '10:00', 'to' => '11:00']);
        Booking::factory()->create(['date' => $this->tomorrow, 'position' => 'P1_VATSIM', 'from' => '12:00', 'to' => '13:00']);

        $atcBookings = $this->subjectUnderTest->getTodaysLiveAtcBookings();

        $this->assertInstanceOf(Collection::class, $atcBookings);
        $this->assertCount(1, $atcBookings);
    }

    #[Test]
    public function it_can_return_a_booking_without_a_known_member()
    {
        Booking::factory()->create([
            'date' => $this->today,
            'from' => '10:00',
            'to' => '11:00',
            'member_id' => 0,
            'type' => 'BK',
        ]);

        $bookings = $this->subjectUnderTest->getTodaysBookings();

        $this->assertEquals('', $bookings->first()['member']['id']);
        $this->assertEquals('Unknown', $bookings->first()['member']['name']);
    }

    #[Test]
    public function it_returns_bookings_in_start_time_order()
    {
        // Ensure these are "live ATC" positions so both methods return same set
        $afternoon = Booking::factory()->create(['date' => $this->today, 'from' => '16:00', 'to' => '17:00', 'type' => 'BK', 'position' => 'EGLL_TWR']);
        $morning = Booking::factory()->create(['date' => $this->today, 'from' => '09:00', 'to' => '11:00', 'type' => 'BK', 'position' => 'EGLL_TWR']);
        $night = Booking::factory()->create(['date' => $this->today, 'from' => '22:00', 'to' => '23:00', 'type' => 'BK', 'position' => 'EGLL_TWR']);

        $todaysBookings = $this->subjectUnderTest->getTodaysBookings();
        $todaysAtcBookings = $this->subjectUnderTest->getTodaysLiveAtcBookings();

        $this->assertEquals($todaysBookings, $todaysAtcBookings);
        $this->assertEquals($morning->id, $todaysBookings[0]['id']);
        $this->assertEquals($afternoon->id, $todaysBookings[1]['id']);
        $this->assertEquals($night->id, $todaysBookings[2]['id']);
    }
}
