<?php

namespace Tests\Unit\Airport;

use App\Models\Airport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AirportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function itCanCreateANewAirport()
    {
        $airport = factory(Airport::class)->create();
        $this->assertInstanceOf(Airport::class, $airport);
        $this->assertNotNull(Airport::find($airport->id));
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
