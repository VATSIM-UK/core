<?php

namespace Tests\Unit\FTE;

use App\Models\Smartcars\Pirep;
use App\Models\Smartcars\Posrep;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PirepModelTest extends TestCase
{
    use DatabaseTransactions;

    private $pirep;

    public function setUp(): void
    {
        parent::setUp();

        $this->pirep = factory(Pirep::class)->create([
            'passed' => null,
            'pass_reason' => null,
        ]);
    }

    public function testItCanBeMarkedPassed()
    {
        $this->pirep->markPassed();
        $this->pirep->save();

        $this->assertEquals('Flight passed all criteria.', $this->pirep->fresh()->pass_reason);
        $this->assertTrue($this->pirep->fresh()->passed);

        $this->assertDatabaseHas('smartcars_pirep', [
            'id' => $this->pirep->id,
            'pass_reason' => $this->pirep->fresh()->pass_reason,
            'passed' => true,
        ]);
    }

    public function testItCanBeMarkedFailedWithoutPosrep()
    {
        $this->pirep->markFailed('It went wrong');
        $this->pirep->save();

        $this->assertEquals('It went wrong', $this->pirep->fresh()->pass_reason);
        $this->assertFalse($this->pirep->fresh()->passed);

        $this->assertDatabaseHas('smartcars_pirep', [
            'id' => $this->pirep->id,
            'pass_reason' => $this->pirep->fresh()->pass_reason,
            'passed' => false,
        ]);
    }

    public function testItCanBeMarkedFailedWithPosrep()
    {
        $posrep = factory(Posrep::class)->create([
            'bid_id' => $this->pirep->bid->id,
        ]);

        $this->pirep->markFailed('It went wrong', $posrep->id);
        $this->pirep->save();

        $this->assertEquals('It went wrong', $this->pirep->fresh()->pass_reason);
        $this->assertFalse($this->pirep->fresh()->passed);

        $this->assertDatabaseHas('smartcars_pirep', [
            'id' => $this->pirep->id,
            'pass_reason' => $this->pirep->fresh()->pass_reason,
            'passed' => false,
            'failed_at' => $posrep->id,
        ]);

        $this->assertEquals($posrep->id, $this->pirep->fresh()->failedAt->id);
    }
}
