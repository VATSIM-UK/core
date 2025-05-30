<?php

namespace Tests\Unit\AirfieldInformation;

use App\Models\Airport;
use App\Models\Airport\Navaid;
use App\Models\Airport\Procedure;
use App\Models\Airport\Runway;
use App\Models\Atc\Position;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AirportTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_can_create_a_new_airport()
    {
        $airport = factory(Airport::class)->create();
        $this->assertInstanceOf(Airport::class, $airport);
        $this->assertInstanceOf(Airport::class, Airport::find($airport->id));
    }

    #[Test]
    public function it_has_working_navaids_relationship()
    {
        $airport = factory(Airport::class)->create();
        $this->assertCount(0, $airport->navaids);

        factory(Navaid::class)->create(['airport_id' => $airport->id]);
        factory(Navaid::class)->create(['airport_id' => $airport->id]);

        $this->assertInstanceOf(Navaid::class, $airport->fresh()->navaids->first());
        $this->assertCount(2, $airport->fresh()->navaids);
    }

    #[Test]
    public function it_has_working_procedures_relationship()
    {
        $airport = factory(Airport::class)->create();
        $this->assertCount(0, $airport->procedures);

        factory(Procedure::class)->create(['airport_id' => $airport->id]);
        factory(Procedure::class)->create(['airport_id' => $airport->id]);

        $this->assertInstanceOf(Procedure::class, $airport->fresh()->procedures->first());
        $this->assertCount(2, $airport->fresh()->procedures);
    }

    #[Test]
    public function it_has_working_runways_relationship()
    {
        $airport = factory(Airport::class)->create();
        $this->assertCount(0, $airport->runways);

        factory(Runway::class)->create(['airport_id' => $airport->id]);
        factory(Runway::class)->create(['airport_id' => $airport->id]);

        $this->assertInstanceOf(Runway::class, $airport->fresh()->runways->first());
        $this->assertCount(2, $airport->fresh()->runways);
    }

    #[Test]
    public function it_has_working_positions_relationship()
    {
        $airport = factory(Airport::class)->create();
        $station1 = Position::factory()->create();
        $station2 = Position::factory()->create();
        $airport->positions()->attach([$station1->id, $station2->id]);
        $airport = $airport->fresh();

        $this->assertInstanceOf(Position::class, $airport->positions->first());
        $this->assertCount(2, $airport->positions);
    }

    #[Test]
    public function it_returns_fir_type()
    {
        $airport = factory(Airport::class)->create(['fir_type' => Airport::FIR_TYPE_EGTT]);
        $this->assertEquals('EGTT', $airport->fir_type);
    }

    #[Test]
    public function it_returns_if_it_has_basic_procedures_correctly()
    {
        $airport = factory(Airport::class)->create(['departure_procedures' => null, 'arrival_procedures' => null, 'vfr_procedures' => null]);
        $this->assertFalse($airport->hasProcedures());
        $airport->departure_procedures = 'Procedure here';
        $airport->save();

        $this->assertTrue($airport->fresh()->hasProcedures());
    }
}
