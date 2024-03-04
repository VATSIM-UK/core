<?php

namespace Tests\Unit\Atc;

use App\Models\Airport;
use App\Models\Atc\Position;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PositionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_create_position()
    {
        $station = Position::factory()->create();
        $this->assertInstanceOf(Position::class, $station);
    }

    public function test_detects_as_temporarily_endorsable()
    {
        $position = Position::factory()->create([
            'temporarily_endorsable' => true,
        ]);

        $result = $position->isTemporarilyEndorsable();

        $this->assertTrue($result);
    }

    public function test_defaults_to_not_temporarily_endorsable()
    {
        $position = Position::factory()->create();

        $result = $position->isTemporarilyEndorsable();

        $this->assertFalse($result);
    }

    public function test_can_have_airport_relationship()
    {
        $station = Position::factory()->create();
        $airport1 = factory(Airport::class)->create();
        $airport2 = factory(Airport::class)->create();
        $station->airports()->attach([$airport1->id, $airport2->id]);
        $station = $station->fresh();

        $this->assertInstanceOf(Airport::class, $station->airports->first());
        $this->assertCount(2, $station->airports);
    }

    public function test_returns_type()
    {
        $station = Position::factory()->create(['type' => Position::TYPE_APPROACH]);
        $this->assertEquals('Approach/Radar', $station->type);
    }

    public function test_retrieves_only_temporarily_endorsable()
    {
        $position = Position::factory()->temporarilyEndorsable()->create();

        $nonEndorsablePosition = Position::factory()->create([
            'temporarily_endorsable' => false,
        ]);

        $result = Position::temporarilyEndorsable()->get();

        $this->assertTrue($result->contains($position));

        $this->assertFalse($result->contains($nonEndorsablePosition));
    }
}
