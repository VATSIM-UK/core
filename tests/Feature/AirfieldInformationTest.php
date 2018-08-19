<?php

namespace Tests\Feature;

use App\Models\Airport;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AirfieldInformationTest extends TestCase
{
    use RefreshDatabase;

    private $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = factory(Account::class)->create();
    }

    /** @test */
    public function itLoadsTheAirportPageAsGuestOrLoggedInUser()
    {
        $airport = factory(Airport::class)->create();
        $this->actingAs($this->account)->get(route('site.airport.view', $airport->icao))->assertSuccessful();
        $this->get(route('site.airport.view', $airport->icao))->assertSuccessful();
    }
}
