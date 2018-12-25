<?php

namespace Tests\Unit\Airport;

use Tests\TestCase;
use App\Models\Airport;
use App\Models\Airport\Runway;
use App\Models\Airport\Procedure;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProcedureTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itCanCreateANewProcedure()
    {
        $procedure = factory(Procedure::class)->create();
        $this->assertInstanceOf(Procedure::class, $procedure);
        $this->assertInstanceOf(Procedure::class, Procedure::find($procedure->id));
    }

    /** @test */
    public function itHasWorkingAirportRelationship()
    {
        $procedure = factory(Procedure::class)->create();
        $this->assertInstanceOf(Airport::class, $procedure->airport);
    }

    /** @test */
    public function itHasWorkingRunwayRelationship()
    {
        $procedure = factory(Procedure::class)->create();
        $this->assertInstanceOf(Runway::class, $procedure->runway);
    }

    /** @test */
    public function itReturnsProcedureType()
    {
        $procedure = factory(Procedure::class)->create(['type' => Procedure::TYPE_SID]);
        $this->assertEquals('SID', $procedure->procedure_type);
    }
}
