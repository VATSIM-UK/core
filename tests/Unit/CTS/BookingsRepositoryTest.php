<?php

namespace Tests\Unit\CTS;

use App\Models\Cts\Booking;
use App\Models\Cts\Member;
use App\Repositories\Cts\BookingRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;

class BookingsRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /* @var BookingRepository */
    protected $subjectUnderTest;

    /* @var Carbon */
    protected $today;

    /* @var Carbon */
    protected $tomorrow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subjectUnderTest = resolve(BookingRepository::class);
        $this->today = $this->knownDate->toDateString();
        $this->tomorrow = $this->knownDate->copy()->addDay()->toDateString();
    }

    /** @test */
    public function it_can_return_a_list_of_bookings_for_today()
    {
        factory(Booking::class, 10)->create(['date' => Carbon::now()]);

        $bookings = $this->subjectUnderTest->getBookings(Carbon::parse($this->today));

        $this->assertInstanceOf(Collection::class, $bookings);
        $this->assertCount(10, $bookings);
        $this->assertInstanceOf(Booking::class, $bookings->first());
    }

    /** @test */
    public function it_can_return_a_list_of_todays_bookings_with_owner_and_type()
    {
        factory(Booking::class, 2)->create(['date' => $this->knownDate->copy()->addDays(5)->toDateString()]);

        $bookingTodayOne = factory(Booking::class)->create([
            'id' => '96155',
            'date' => $this->today,
            'from' => '17:00',
            'member_id' => factory(Member::class)->create()->id,
            'type' => 'BK',
        ]);

        $bookingTodayTwo = factory(Booking::class)->create([
            'id' => '96156',
            'date' => $this->today,
            'from' => '18:00',
            'member_id' => factory(Member::class)->create()->id,
            'type' => 'ME',
        ]);

        $bookings = $this->subjectUnderTest->getTodaysBookings();

        $this->assertInstanceOf(Collection::class, $bookings);
        $this->assertCount(2, $bookings);

        $this->assertEquals([
            'id' => $bookingTodayOne->id,
            'date' => $this->today,
            'from' => Carbon::parse($bookingTodayOne->from)->format('H:i'),
            'to' => Carbon::parse($bookingTodayOne->to)->format('H:i'),
            'position' => $bookingTodayOne->position,
            'type' => $bookingTodayOne->type,
            'member' => [
                'id' => $bookingTodayOne['member']['cid'],
                'name' => $bookingTodayOne['member']['name'],
            ],
        ], $bookings->get(0)->toArray());
        $this->assertEquals([
            'id' => $bookingTodayTwo->id,
            'date' => $this->today,
            'from' => Carbon::parse($bookingTodayTwo->from)->format('H:i'),
            'to' => Carbon::parse($bookingTodayTwo->to)->format('H:i'),
            'position' => $bookingTodayTwo->position,
            'type' => $bookingTodayTwo->type,
            'member' => [
                'id' => $bookingTodayTwo['member']['cid'],
                'name' => $bookingTodayTwo['member']['name'],
            ],
        ], $bookings->get(1)->toArray());
    }

    /** @test */
    public function it_hides_member_details_on_exam_booking()
    {
        $normalBooking = factory(Booking::class)->create(['date' => $this->today, 'from' => '17:00', 'type' => 'BK']);
        factory(Booking::class)->create(['date' => $this->today, 'from' => '18:00', 'type' => 'EX']);

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

    /** @test */
    public function it_can_return_a_list_of_todays_live_atc_bookings()
    {
        factory(Booking::class)->create(['date' => $this->today, 'position' => 'EGKK_APP']); // Live ATC booking today
        factory(Booking::class)->create(['date' => $this->today, 'position' => 'EGKK_SBAT']); // Sweatbox ATC booking today
        factory(Booking::class)->create(['date' => $this->today, 'position' => 'P1_VATSIM']); // Pilot booking today
        factory(Booking::class)->create(['date' => $this->tomorrow, 'position' => 'EGKK_APP']); // ATC booking tomorrow
        factory(Booking::class)->create(['date' => $this->tomorrow, 'position' => 'P1_VATSIM']); // Pilot booking tomorrw

        $atcBookings = $this->subjectUnderTest->getTodaysLiveAtcBookings();

        $this->assertInstanceOf(Collection::class, $atcBookings);
        $this->assertCount(1, $atcBookings);
    }

    /** @test */
    public function it_can_return_a_booking_without_a_known_member()
    {
        factory(Booking::class)->create(['date' => $this->today, 'member_id' => 0, 'type' => 'BK']);

        $this->subjectUnderTest->getTodaysLiveAtcBookings();

        $this->expectNotToPerformAssertions();
    }

    /** @test */
    public function it_returns_bookings_in_start_time_order()
    {
        $afternoon = factory(Booking::class)->create(['date' => $this->today, 'from' => '16:00', 'to' => '17:00', 'type' => 'BK']);
        $morning = factory(Booking::class)->create(['date' => $this->today, 'from' => '09:00', 'to' => '11:00', 'type' => 'BK']);
        $night = factory(Booking::class)->create(['date' => $this->today, 'from' => '22:00', 'to' => '23:00', 'type' => 'BK']);

        $todaysBookings = $this->subjectUnderTest->getTodaysBookings();
        $todaysAtcBookings = $this->subjectUnderTest->getTodaysLiveAtcBookings();

        $this->assertEquals($todaysBookings, $todaysAtcBookings);
        $this->assertEquals($morning->id, $todaysBookings[0]['id']);
        $this->assertEquals($afternoon->id, $todaysBookings[1]['id']);
        $this->assertEquals($night->id, $todaysBookings[2]['id']);
    }
}
