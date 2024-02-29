<?php

namespace Tests\Feature\Site;

use App\Models\Cts\Booking;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function testItLoadsTheHomepage()
    {
        $this->get(route('site.home'))->assertOk();
    }

    /** @test */
    public function testItShowsLiveAtcBookingsOnTheHomepage()
    {
        $this->withoutExceptionHandling();
        $booking = factory(Booking::class)->create([
            'date' => $this->knownDate->toDateString(),
            'position' => 'EGKK_APP',
            'type' => 'BK',
        ]);

        $url = "https://cts.vatsim.uk/bookings/bookinfo.php?cb={$booking->id}";

        $this->get(route('site.home'))
            ->assertSee($url)
            ->assertSee($booking->position)
            ->assertSee(Carbon::parse($booking->from)->format('H:i'))
            ->assertSee(Carbon::parse($booking->to)->format('H:i'))
            ->assertSee(e($booking->member->name))
            ->assertSee($booking->member->cid);
    }

    /** @test */
    public function testItDoesntShowExamCandidatesOnTheHomepage()
    {
        $booking = factory(Booking::class)->create([
            'date' => $this->knownDate->toDateString(),
            'position' => 'EGKK_APP',
            'type' => 'EX',
        ]);

        $this->get(route('site.home'))
            ->assertDontSee(e($booking->member->name))
            ->assertDontSee($booking->member->cid)
            ->assertSee('Hidden');
    }

    /** @test */
    public function testItDoesntShowPilotOrSweatboxBookingsOnTheHomepage()
    {
        $pilot = factory(Booking::class)->create([
            'date' => $this->knownDate->toDateString(),
            'position' => 'P1_VATSIM',
            'type' => 'BK',
        ]);

        $sweatbox = factory(Booking::class)->create([
            'date' => $this->knownDate->toDateString(),
            'position' => 'EGKK_SBAT',
            'type' => 'BK',
        ]);

        $this->get(route('site.home'))
            ->assertDontSee($pilot->position)
            ->assertDontSee($sweatbox->position);
    }
}
