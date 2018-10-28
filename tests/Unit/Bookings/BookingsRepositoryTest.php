<?php

namespace Tests\Unit\Bookings;

use App\Models\Cts\Booking;
use App\Models\Cts\Member;
use App\Repositories\Cts\BookingRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\UnitTestCase;

class BookingsRepositoryTest extends UnitTestCase
{
    use DatabaseTransactions;

    /* @var BookingsRespository */
    protected $subjectUnderTest;

    protected function setUp()
    {
        parent::setUp();

        $this->subjectUnderTest = resolve(BookingRepository::class);
    }

    /*
     * @group CTS Bookings
     * @test
     */
    public function test_it_can_return_a_list_of_todays_bookings_with_owner_and_type()
    {
        factory(Booking::class, 2)->create(['date' => $this->knownDate->copy()->addDays(5)->toDateString()]);

        $bookingTodayOne = factory(Booking::class)->create([
            'date' => $this->knownDate->toDateString(),
            'member_id' => factory(Member::class)->create()->id,
            'type' => 'BK'
        ]);

        $bookingTodayTwo = factory(Booking::class)->create([
            'date' => $this->knownDate->toDateString(),
            'member_id' => factory(Member::class)->create()->id,
            'type' => 'ME'
        ]);

        $bookings = $this->subjectUnderTest->getTodaysBookings();

        $this->assertInstanceOf(Collection::class, $bookings);
        $this->assertCount(2, $bookings);
        $this->assertEquals([
            'date' => $this->knownDate->toDateString(),
            'from' => $bookingTodayOne->from,
            'to' => $bookingTodayOne->to,
            'position' => $bookingTodayOne->position,
            'member' => [
                'id' => $bookingTodayOne['member']['cid'],
                'name' => $bookingTodayOne['member']['name'],
            ],
            'type' => $bookingTodayOne->type,
        ], $bookings->get(0)->toArray());
        $this->assertEquals([
            'date' => $this->knownDate->toDateString(),
            'from' => $bookingTodayTwo->from,
            'to' => $bookingTodayTwo->to,
            'position' => $bookingTodayTwo->position,
            'member' => [
                'id' => $bookingTodayTwo['member']['cid'],
                'name' => $bookingTodayTwo['member']['name'],
            ],
            'type' => $bookingTodayTwo->type,
        ], $bookings->get(1)->toArray());
    }

    /*
     * @group CTS Bookings
     * @test
     */
    public function test_it_hides_member_details_on_exam_booking()
    {
        $normalBooking = factory(Booking::class)->create(['date' => $this->knownDate->toDateString(), 'type' => 'BK']);
        factory(Booking::class)->create(['date' => $this->knownDate->toDateString(), 'type' => 'EX']);

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
}
