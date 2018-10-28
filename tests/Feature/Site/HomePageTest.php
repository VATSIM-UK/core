<?php

namespace Tests\Feature\Site;

use App\Models\Cts\Booking;
use Tests\TestCase;

class HomePageTest extends TestCase
{

    /** @test * */
    public function itLoadsTheHomepage()
    {
        $this->get(route('site.home'))->assertOk();
    }

    /** @test * */
    public function itShowsLiveAtcBookingsOnTheHomepage()
    {
        $booking = factory(Booking::class)->create(['date' => $this->knownDate->toDateString(), 'position' => 'EGKK_APP', 'type' => 'BK']);

        $this->get(route('site.home'))
            ->assertSee($booking->position)
            ->assertSee($booking->from)
            ->assertSee($booking->to)
            ->assertSee($booking->member->name)
            ->assertSee($booking->member->cid);
    }


}
