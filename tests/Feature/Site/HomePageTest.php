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
    public function test_it_loads_the_homepage()
    {
        $this->get(route('site.home'))->assertOk();
    }

    /** @test */
    public function test_it_shows_live_atc_bookings_on_the_homepage()
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
            ->assertDontSee(e($booking->member->name))
            ->assertSee($booking->member->cid);
    }

    /** @test */
    public function test_it_doesnt_show_exam_candidates_on_the_homepage()
    {
        $booking = factory(Booking::class)->create([
            'date' => $this->knownDate->toDateString(),
            'position' => 'EGKK_APP',
            'type' => 'EX',
        ]);

        $this->get(route('site.home'))
            ->assertDontSee(e($booking->member->name))
            ->assertDontSee($booking->member->cid);
    }

    /** @test */
    public function test_it_doesnt_show_pilot_or_sweatbox_bookings_on_the_homepage()
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
