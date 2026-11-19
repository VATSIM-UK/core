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

    #[Test]
    public function test_it_escapes_staff_authored_airport_text()
    {
        $airport = factory(Airport::class)->create([
            'description' => "Line one\n<script>alert(1)</script>",
            'departure_procedures' => '<p>Climb straight ahead</p><script>alert(2)</script>',
            'other_information' => '<p>Extra info</p><a href="javascript:alert(3)">click</a>',
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($airport) {
            $mock->shouldReceive('getStandStatus')
                ->with(Str::upper($airport->icao))
                ->once()
                ->andReturn([]);
        });

        $this->get(route('site.airport.view', $airport->icao))
            ->assertSuccessful()
            // Plain text: escaped, with line breaks preserved.
            ->assertSee('Line one<br />', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            // Rich text: markup kept, scripts and unsafe URLs removed.
            ->assertSee('<p>Climb straight ahead</p>', false)
            ->assertSee('<p>Extra info</p>', false)
            ->assertDontSee('<script>alert(2)</script>', false)
            ->assertDontSee('javascript:alert(3)', false);
    }
}
