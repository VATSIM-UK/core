<?php

namespace Tests\Unit\AirfieldInformation;

use App\Models\Airport;
use App\Models\Airport\Procedure;
use App\Models\Airport\Runway;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcedureTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_can_create_a_new_procedure()
    {
        $procedure = factory(Procedure::class)->create();
        $this->assertInstanceOf(Procedure::class, $procedure);
        $this->assertInstanceOf(Procedure::class, Procedure::find($procedure->id));
    }

    #[Test]
    public function it_has_working_airport_relationship()
    {
        $procedure = factory(Procedure::class)->create();
        $this->assertInstanceOf(Airport::class, $procedure->airport);
    }

    #[Test]
    public function it_has_working_runway_relationship()
    {
        $procedure = factory(Procedure::class)->create();
        $this->assertInstanceOf(Runway::class, $procedure->runway);
    }

    #[Test]
    public function it_returns_procedure_type()
    {
        $procedure = factory(Procedure::class)->create(['type' => Procedure::TYPE_SID]);
        $this->assertEquals('SID', $procedure->procedure_type);
    }
}
