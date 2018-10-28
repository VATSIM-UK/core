<?php

namespace Tests\Unit\Bookings;

use App\Models\Cts\Booking;
use App\Models\Cts\Member;
use App\Repositories\Cts\BookingRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\UnitTestCase;

class BookingsRepositoryTest extends UnitTestCase
{
    use DatabaseTransactions;

    /* @var BookingsRespository */
    protected $subjectUnderTest;

    /* @var Carbon */
    protected $today;

    /* @var Carbon */
    protected $tomorrow;

    protected function setUp()
    {
        parent::setUp();

        $this->subjectUnderTest = resolve(BookingRepository::class);
        $this->today = $this->knownDate->toDateString();
        $this->tomorrow = $this->knownDate->copy()->addDay()->toDateString();
    }

    /* @test */
    public function test_it_can_return_a_list_of_todays_bookings_with_owner_and_type()
    {
        factory(Booking::class, 2)->create(['date' => $this->knownDate->copy()->addDays(5)->toDateString()]);

        $bookingTodayOne = factory(Booking::class)->create([
            'date' => $this->today,
            'member_id' => factory(Member::class)->create()->id,
            'type' => 'BK',
        ]);

        $bookingTodayTwo = factory(Booking::class)->create([
            'date' => $this->today,
            'member_id' => factory(Member::class)->create()->id,
            'type' => 'ME',
        ]);

        $bookings = $this->subjectUnderTest->getTodaysBookings();

        $this->assertInstanceOf(Collection::class, $bookings);
        $this->assertCount(2, $bookings);
        $this->assertEquals([
            'date' => $this->today,
            'from' => Carbon::parse($bookingTodayOne->from)->format('H:i'),
            'to' => Carbon::parse($bookingTodayOne->to)->format('H:i'),
            'position' => $bookingTodayOne->position,
            'member' => [
                'id' => $bookingTodayOne['member']['cid'],
                'name' => $bookingTodayOne['member']['name'],
            ],
            'type' => $bookingTodayOne->type,
        ], $bookings->get(0)->toArray());
        $this->assertEquals([
            'date' => $this->today,
            'from' => Carbon::parse($bookingTodayTwo->from)->format('H:i'),
            'to' => Carbon::parse($bookingTodayTwo->to)->format('H:i'),
            'position' => $bookingTodayTwo->position,
            'member' => [
                'id' => $bookingTodayTwo['member']['cid'],
                'name' => $bookingTodayTwo['member']['name'],
            ],
            'type' => $bookingTodayTwo->type,
        ], $bookings->get(1)->toArray());
    }

    /* @test */
    public function test_it_hides_member_details_on_exam_booking()
    {
        $normalBooking = factory(Booking::class)->create(['date' => $this->today, 'type' => 'BK']);
        factory(Booking::class)->create(['date' => $this->today, 'type' => 'EX']);

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

    /* @test */
    public function test_it_can_return_a_list_of_todays_live_atc_bookings()
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

    /* @test */
    public function test_it_can_return_a_booking_without_a_known_member()
    {
        factory(Booking::class)->create(['date' => $this->today, 'member_id' => 0, 'type' => 'BK']);

        $this->subjectUnderTest->getTodaysLiveAtcBookings();

        $this->expectNotToPerformAssertions();
    }
}
