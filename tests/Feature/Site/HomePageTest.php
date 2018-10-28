<?php

namespace Tests\Feature\Site;

use App\Models\Cts\Booking;
use Tests\TestCase;
use Carbon\Carbon;

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
            ->assertSee(Carbon::parse($booking->from)->format('H:i'))
            ->assertSee(Carbon::parse($booking->to)->format('H:i'))
            ->assertSee($booking->member->name)
            ->assertSee($booking->member->cid);
    }

    /** @test * */
    public function itDoesntShowExamCandidatesOnTheHomepage()
    {
        $booking = factory(Booking::class)->create(['date' => $this->knownDate->toDateString(), 'position' => 'EGKK_APP', 'type' => 'EX']);

        $this->get(route('site.home'))
            ->assertDontSee($booking->member->name)
            ->assertDontSee($booking->member->cid)
            ->assertSee('Hidden');
    }

    /** @test * */
    public function itDoesntShowPilotOrSweatboxBookingsOnTheHomepage()
    {
        $pilot = factory(Booking::class)->create(['date' => $this->knownDate->toDateString(), 'position' => 'P1_VATSIM', 'type' => 'BK']);
        $sweatbox = factory(Booking::class)->create(['date' => $this->knownDate->toDateString(), 'position' => 'EGKK_SBAT', 'type' => 'BK']);

        $this->get(route('site.home'))
            ->assertDontSee($pilot->position)
            ->assertDontSee($sweatbox->position);
    }


}
