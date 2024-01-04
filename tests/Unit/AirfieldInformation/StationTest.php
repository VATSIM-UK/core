<?php

namespace Tests\Unit\AirfieldInformation;

use App\Models\Airport;
use App\Models\Atc\Position;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itCanCreateANewStation()
    {
        $station = factory(Position::class)->create();
        $this->assertInstanceOf(Position::class, $station);
    }

    /** @test */
    public function itHasWorkingAirportsRelationship()
    {
        $station = factory(Position::class)->create();
        $airport1 = factory(Airport::class)->create();
        $airport2 = factory(Airport::class)->create();
        $station->airports()->attach([$airport1->id, $airport2->id]);
        $station = $station->fresh();

        $this->assertInstanceOf(Airport::class, $station->airports->first());
        $this->assertCount(2, $station->airports);
    }

    /** @test */
    public function itReturnsType()
    {
        $station = factory(Position::class)->create(['type' => Position::TYPE_APPROACH]);
        $this->assertEquals('Approach/Radar', $station->type);
    }
}
