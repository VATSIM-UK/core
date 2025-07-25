<?php

namespace Tests\Unit\AirfieldInformation;

use App\Models\Airport;
use App\Models\Airport\Procedure;
use App\Models\Airport\Runway;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RunwayTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_can_create_a_new_runway()
    {
        $runway = factory(Runway::class)->create();
        $this->assertInstanceOf(Runway::class, $runway);
        $this->assertInstanceOf(Runway::class, Runway::find($runway->id));
    }

    #[Test]
    public function it_has_working_airport_relationship()
    {
        $runway = factory(Runway::class)->create();
        $this->assertInstanceOf(Airport::class, $runway->airport);
    }

    #[Test]
    public function it_has_working_procedures_relationship()
    {
        $runway = factory(Runway::class)->create();
        factory(Procedure::class)->create(['runway_id' => $runway->id]);
        $this->assertInstanceOf(Procedure::class, $runway->fresh()->procedures->first());
    }

    #[Test]
    public function it_returns_surface_type()
    {
        $runway = factory(Runway::class)->create(['surface_type' => Runway::SURFACE_TYPE_CONCRETE]);
        $this->assertEquals('Concrete', $runway->surface_type);
    }
}
