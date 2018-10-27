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
        Booking::create(['date' => '2010-01-01', 'from' => '11:00:00', 'to' => '12:00:00', 'position' => 'EGKK_APP', 'member_id' => '258635', 'type' => 'BK']);
        Booking::create(['date' => '2010-01-01', 'from' => '12:00:00', 'to' => '13:00:00', 'position' => 'EGCC_APP', 'member_id' => '317737', 'type' => 'ME']);
        Booking::create(['date' => $this->knownDate->toDateString(), 'from' => '11:00:00', 'to' => '12:00:00', 'position' => 'EGKK_APP', 'member_id' => '258635', 'type' => 'BK']);
        Booking::create(['date' => $this->knownDate->toDateString(), 'from' => '12:00:00', 'to' => '13:00:00', 'position' => 'EGCC_APP', 'member_id' => '317737', 'type' => 'ME']);

        Member::create(['id' => '258635', 'cid' => '1258635', 'name' => 'Calum Towers', 'joined' => $this->knownDate->toDateString(), 'joined_div' => $this->knownDate->toDateString()]);
        Member::create(['id' => '317737', 'cid' => '1317737', 'name' => 'Daniel Crookes', 'joined' => $this->knownDate->toDateString(), 'joined_div' => $this->knownDate->toDateString()]);

        $bookings = $this->subjectUnderTest->getTodaysBookings();

        $this->assertInstanceOf(Collection::class, $bookings);
        $this->assertCount(2, $bookings);
        $this->assertEquals([
            'date' => $this->knownDate->toDateString(),
            'from' => '11:00:00',
            'to' => '12:00:00',
            'position' => 'EGKK_APP',
            'member' => [
                'id' => 1258635,
                'name' => 'Calum Towers',
            ],
            'type' => 'BK',
        ], $bookings->get(0)->toArray());
        $this->assertEquals([
            'date' => $this->knownDate->toDateString(),
            'from' => '12:00:00',
            'to' => '13:00:00',
            'position' => 'EGCC_APP',
            'member' => [
                'id' => 1317737,
                'name' => 'Daniel Crookes',
            ],
            'type' => 'ME',
        ], $bookings->get(1)->toArray());
    }

    /*
     * @group CTS Bookings
     * @test
     */
    public function test_it_hides_member_details_on_exam_booking()
    {
        Booking::create(['date' => $this->knownDate->toDateString(), 'from' => '11:00:00', 'to' => '12:00:00', 'position' => 'EGKK_APP', 'member_id' => '258635', 'type' => 'BK']);
        Booking::create(['date' => $this->knownDate->toDateString(), 'from' => '12:00:00', 'to' => '13:00:00', 'position' => 'EGCC_APP', 'member_id' => '317737', 'type' => 'EX']);

        Member::create(['id' => '258635', 'cid' => '1258635', 'name' => 'Calum Towers', 'joined' => $this->knownDate->toDateString(), 'joined_div' => $this->knownDate->toDateString()]);
        Member::create(['id' => '317737', 'cid' => '1317737', 'name' => 'Daniel Crookes', 'joined' => $this->knownDate->toDateString(), 'joined_div' => $this->knownDate->toDateString()]);

        $bookings = $this->subjectUnderTest->getTodaysBookings();

        $this->assertEquals([
            'id' => 1258635,
            'name' => 'Calum Towers',
        ], $bookings->get(0)['member']);

        $this->assertEquals([
            'id' => '',
            'name' => 'Hidden',
        ], $bookings->get(1)['member']);
    }
}
