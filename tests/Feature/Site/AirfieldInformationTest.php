<?php

namespace Tests\Feature\Site;

use App\Models\Airport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AirfieldInformationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function testItLoadsTheAirportPage()
    {
        $airport = factory(Airport::class)->create();

        // Load as logged in user
        $this->actingAs($this->user)
            ->get(route('site.airport.view', $airport->icao))
            ->assertSuccessful();

        // Load as guest
        $this->get(route('site.airport.view', $airport->icao))
            ->assertSuccessful();
    }
}
