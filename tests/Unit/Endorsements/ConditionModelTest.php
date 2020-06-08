<?php

namespace Tests\Unit\Endorsements;

use App\Models\Atc\Endorsement;
use App\Models\Atc\Endorsement\Condition;
use App\Models\NetworkData\Atc;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ConditionModelTest extends TestCase
{
    use DatabaseTransactions;

    private $condition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condition = factory(Condition::class)->create();
    }

    /** @test */
    public function itCanBeCreated()
    {
        $condition = Condition::create([
            'endorsement_id' => 100,
            'positions' => ['EGLL_TWR', 'EGPH_%'],
            'required_hours' => 10,
            'type' => Condition::TYPE_SUM_OF_AIRFIELDS,
            'within_months' => null,
        ]);

        $this->assertDatabaseHas('endorsement_conditions', [
            'id' => $condition->id,
            'endorsement_id' => 100,
            'positions' => json_encode(['EGLL_TWR', 'EGPH_%']),
            'required_hours' => 10,
            'type' => Condition::TYPE_SUM_OF_AIRFIELDS,
            'within_months' => null,
        ]);
    }

    /** @test */
    public function itReturnsAnArrayOfPositions()
    {
        $this->assertTrue(is_array($this->condition->positions));
    }

    /** @test */
    public function itReturnsAListOfHumanPositions()
    {
        $this->condition->positions = ['EGKK_%', 'EGLL_%'];
        $this->condition->save();

        $this->assertTrue(is_array($this->condition->fresh()->human_positions));
        $this->assertEquals(['EGKK_XXX', 'EGLL_XXX'], $this->condition->fresh()->human_positions);
    }

    /** @test */
    public function itCanBeAssociatedWithAEndorsement()
    {
        $endorsement = factory(Endorsement::class)->create();
        $condition = factory(Condition::class)->make(['endorsement_id' => null]);

        $this->assertNull($condition->endorsement);
        $condition->endorsement()->associate($endorsement);
        $condition->save();

        $this->assertEquals($endorsement->id, $condition->fresh()->endorsement->id);
    }

    /** @test */
    public function itCorrectlyReportsProgress()
    {
        $condition = factory(Endorsement\Condition::class)->make(['positions' => ['EGLL_%', 'ESSEX_APP'], 'required_hours' => 10, 'within_months' => 2, 'type' => Endorsement\Condition::TYPE_ON_SINGLE_AIRFIELD]);

        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'EGLL_TWR',
            'minutes_online' => 60,
            'connected_at' => Carbon::now()->subMonths(3),
            'disconnected_at' => Carbon::now()->subMonths(3),
        ]);
        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'EGLL_TWR',
            'minutes_online' => 60,
            'connected_at' => Carbon::now()->subMonths(1),
            'disconnected_at' => Carbon::now()->subMonths(1),
        ]);
        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'ESSEX_TWR',
            'minutes_online' => 60,
            'connected_at' => Carbon::now()->subMonths(1),
            'disconnected_at' => Carbon::now()->subMonths(1),
        ]);
        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'ESSEX_APP',
            'minutes_online' => 120,
            'connected_at' => Carbon::now()->subMonths(1),
            'disconnected_at' => Carbon::now()->subMonths(1),
        ]);

        $data = $condition->progressForUser($this->user);
        $this->assertEquals(['EGLL' => 1, 'ESSEX' => 2], $data->all());

        $condition->within_months = null;
        $condition->save();
        $data = $condition->progressForUser($this->user);
        $this->assertEquals(['EGLL' => 2, 'ESSEX' => 2], $data->all());
    }

    /** @test */
    public function itCorrectlyReportsMetForSingleAirfield()
    {
        $condition = factory(Endorsement\Condition::class)->create(['positions' => ['EGLL_%', 'ESSEX_APP'], 'required_hours' => 10, 'within_months' => 2, 'type' => Endorsement\Condition::TYPE_ON_SINGLE_AIRFIELD]);

        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'EGLL_TWR',
            'minutes_online' => 5 * 60,
            'connected_at' => Carbon::now()->subMonths(1),
            'disconnected_at' => Carbon::now()->subMonths(1),
        ]);
        $session = factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'ESSEX_APP',
            'minutes_online' => 5 * 60,
            'connected_at' => Carbon::now()->subMonths(1),
            'disconnected_at' => Carbon::now()->subMonths(1),
        ]);

        $this->assertFalse($condition->isMetForUser($this->user));

        $session->minutes_online = 10 * 60;
        $session->save();
        $this->assertTrue($condition->fresh()->isMetForUser($this->user));
    }

    /** @test */
    public function itCorrectlyReportsMetForSum()
    {
        $condition = factory(Endorsement\Condition::class)->create(['positions' => ['EGLL_%', 'ESSEX_APP'], 'required_hours' => 10, 'within_months' => 2, 'type' => Endorsement\Condition::TYPE_SUM_OF_AIRFIELDS]);

        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'EGLL_TWR',
            'minutes_online' => 5 * 60,
            'connected_at' => Carbon::now()->subMonths(1),
            'disconnected_at' => Carbon::now()->subMonths(1),
        ]);
        $session = factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'ESSEX_APP',
            'minutes_online' => 4 * 60,
            'connected_at' => Carbon::now()->subMonths(1),
            'disconnected_at' => Carbon::now()->subMonths(1),
        ]);

        $this->assertFalse($condition->isMetForUser($this->user));

        $session->minutes_online = 5 * 60;
        $session->save();
        $this->assertTrue($condition->fresh()->isMetForUser($this->user));
    }
}
