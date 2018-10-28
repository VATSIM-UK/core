<?php

namespace Tests\Feature\Site;

use Alawrence\Ipboard\Ipboard;
use App\Models\Cts\Booking;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SitePageLoadTest extends TestCase
{
    /** @test * */
    public function itLoadsTheHomepage()
    {
        $this->get(route('site.home'))->assertOk();
    }

    /** @test * */
    public function itShowsAtcBookingsOnTheHomepage()
    {
        $booking = factory(Booking::class)->create(['date' => $this->knownDate->toDateString(), 'position' => 'EGKK_APP', 'type' => 'BK']);

        $this->get(route('site.home'))
            ->assertSee($booking->position)
            ->assertSee($booking->from)
            ->assertSee($booking->to)
            ->assertSee($booking->member->name)
            ->assertSee($booking->member->id);
    }

    /** @test * */
    public function itLoadsTheJoinUsPage()
    {
        $this->get(route('site.join'))->assertOk();
    }

    /** @test * */
    public function itLoadsTheStaffPageRegardlessOfIPBKey()
    {
        Config::set([
            'ipboard.api_key' => 'Invalid_API_Key',
        ]);

        $this->get(route('site.staff'))->assertOk();
    }
}
