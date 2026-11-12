<?php

namespace Tests\Feature\Site;

use App\Libraries\UKCP;
use App\Models\Airport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AirfieldInformationTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function test_it_loads_the_airport_page()
    {
        $airport = factory(Airport::class)->create();

        $this->mock(UKCP::class, function (MockInterface $mock) use ($airport) {
            $mock->shouldReceive('getStandStatus')
                ->with(Str::upper($airport->icao))
                ->twice()
                ->andReturn([]);
        });

        // Load as logged in user
        $this->actingAs($this->user)
            ->get(route('site.airport.view', $airport->icao))
            ->assertSuccessful();

        // Load as guest
        $this->get(route('site.airport.view', $airport->icao))
            ->assertSuccessful();
    }
}
