<?php

namespace Tests\Unit\AirfieldInformation;

use App\Models\Airport;
use App\Models\Airport\Navaid;
use App\Models\Airport\Procedure;
use App\Models\Airport\Runway;
use App\Models\Station;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AirportTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itCanCreateANewAirport()
    {
        $airport = factory(Airport::class)->create();
        $this->assertInstanceOf(Airport::class, $airport);
        $this->assertInstanceOf(Airport::class, Airport::find($airport->id));
    }

    /** @test */
    public function itHasWorkingNavaidsRelationship()
    {
        $airport = factory(Airport::class)->create();
        $this->assertCount(0, $airport->navaids);

        factory(Navaid::class)->create(['airport_id' => $airport->id]);
        factory(Navaid::class)->create(['airport_id' => $airport->id]);

        $this->assertInstanceOf(Navaid::class, $airport->fresh()->navaids->first());
        $this->assertCount(2, $airport->fresh()->navaids);
    }

    /** @test */
    public function itHasWorkingProceduresRelationship()
    {
        $airport = factory(Airport::class)->create();
        $this->assertCount(0, $airport->procedures);

        factory(Procedure::class)->create(['airport_id' => $airport->id]);
        factory(Procedure::class)->create(['airport_id' => $airport->id]);

        $this->assertInstanceOf(Procedure::class, $airport->fresh()->procedures->first());
        $this->assertCount(2, $airport->fresh()->procedures);
    }

    /** @test */
    public function itHasWorkingRunwaysRelationship()
    {
        $airport = factory(Airport::class)->create();
        $this->assertCount(0, $airport->runways);

        factory(Runway::class)->create(['airport_id' => $airport->id]);
        factory(Runway::class)->create(['airport_id' => $airport->id]);

        $this->assertInstanceOf(Runway::class, $airport->fresh()->runways->first());
        $this->assertCount(2, $airport->fresh()->runways);
    }

    /** @test */
    public function itHasWorkingStationsRelationship()
    {
        $airport = factory(Airport::class)->create();
        $station1 = factory(Station::class)->create();
        $station2 = factory(Station::class)->create();
        $airport->stations()->attach([$station1->id, $station2->id]);
        $airport = $airport->fresh();

        $this->assertInstanceOf(Station::class, $airport->stations->first());
        $this->assertCount(2, $airport->stations);
    }

    /** @test */
    public function itReturnsFirType()
    {
        $airport = factory(Airport::class)->create(['fir_type' => Airport::FIR_TYPE_EGTT]);
        $this->assertEquals('EGTT', $airport->fir_type);
    }

    /** @test */
    public function itReturnsIfItHasBasicProceduresCorrectly()
    {
        $airport = factory(Airport::class)->create(['departure_procedures' => null, 'arrival_procedures' => null, 'vfr_procedures' => null]);
        $this->assertFalse($airport->hasProcedures());
        $airport->departure_procedures = 'Procedure here';
        $airport->save();
        
        $this->assertTrue($airport->fresh()->hasProcedures());
    }
}
