<?php

namespace Tests\Unit\Airport;

use Tests\TestCase;
use App\Models\Airport;
use App\Models\Airport\Runway;
use App\Models\Airport\Procedure;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RunwayTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itCanCreateANewRunway()
    {
        $runway = factory(Runway::class)->create();
        $this->assertInstanceOf(Runway::class, $runway);
        $this->assertInstanceOf(Runway::class, Runway::find($runway->id));
    }

    /** @test */
    public function itHasWorkingAirportRelationship()
    {
        $runway = factory(Runway::class)->create();
        $this->assertInstanceOf(Airport::class, $runway->airport);
    }

    /** @test */
    public function itHasWorkingProceduresRelationship()
    {
        $runway = factory(Runway::class)->create();
        factory(Procedure::class)->create(['runway_id' => $runway->id]);
        $this->assertInstanceOf(Procedure::class, $runway->fresh()->procedures->first());
    }

    /** @test */
    public function itReturnsSurfaceType()
    {
        $runway = factory(Runway::class)->create(['surface_type' => Runway::SURFACE_TYPE_CONCRETE]);
        $this->assertEquals('Concrete', $runway->surface_type);
    }
}
