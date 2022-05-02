<?php

namespace Tests\Unit\Endorsements;

use App\Models\Atc\Endorsement;
use App\Models\NetworkData\Atc;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EndorsementModelTest extends TestCase
{
    use DatabaseTransactions;

    private $endorsement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->endorsement = factory(Endorsement::class)->create();
    }

    /** @test */
    public function itCanBeCreated()
    {
        $endorsement = Endorsement::create([
            'name' => 'My First Endorsement',
        ]);

        $this->assertDatabaseHas('endorsements', ['id' => $endorsement->id, 'name' => $endorsement->name]);
    }

    /** @test */
    public function itCanBeAssociatedWithACondition()
    {
        $condition = factory(Endorsement\Condition::class)->make(['endorsement_id' => null]);

        $this->assertCount(0, $this->endorsement->fresh()->conditions);
        $this->endorsement->conditions()->save($condition);
        $this->assertEquals($this->endorsement->id, $condition->fresh()->endorsement_id);
        $this->assertEquals($this->endorsement->fresh()->conditions()->first()->id, $condition->id);
        $this->assertCount(1, $this->endorsement->fresh()->conditions);

        $condition = factory(Endorsement\Condition::class)->make(['endorsement_id' => null]);
        $this->endorsement->conditions()->save($condition);
        $this->assertCount(2, $this->endorsement->fresh()->conditions);
    }

    /** @test */
    public function itWillReportConditionsMetWhenAllAreMetForSingleAirfield()
    {
        $this->createMockCondition();

        // create the network data
        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'EGKK_TWR',
            'minutes_online' => 60,
        ]);

        $this->assertTrue($this->endorsement->fresh()->conditionsMetForUser($this->user));
    }

    /** @test */
    public function itWillReportConditionsNotMetWhenNetworkDataNotPresentForSingleAirfield()
    {
        $this->createMockCondition();

        // network data not present for the specified position
        $this->assertFalse($this->endorsement->fresh()->conditionsMetForUser($this->user));
    }

    /** @test */
    public function itWillReportConditionsMetForMultipleAirfieldsWhenNetworkDataPresent()
    {
        $this->createMockCondition(['EGKK_%', 'EGLL_%'], Endorsement\Condition::TYPE_SUM_OF_AIRFIELDS);

        // create the network data
        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'EGKK_TWR',
            'minutes_online' => 30,
        ]);
        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'EGLL_S_TWR',
            'minutes_online' => 30,
        ]);

        // should return true as it sums up to the 60 mins required in the mock condition.
        $this->assertTrue($this->endorsement->fresh()->conditionsMetForUser($this->user));
    }

    /** @test */
    public function itWillReportConditionsNotMetForMultipleAirfieldsWhenRequiredTimeIsntMet()
    {
        $this->createMockCondition(['EGKK_%', 'EGLL_%'], Endorsement\Condition::TYPE_SUM_OF_AIRFIELDS);

        // create the network data
        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'EGKK_TWR',
            'minutes_online' => 10,
        ]);
        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'EGLL_S_TWR',
            'minutes_online' => 10,
        ]);

        $this->assertFalse($this->endorsement->fresh()->conditionsMetForUser($this->user));
    }

    /** @test */
    public function itShouldReturnValueFromCacheDuringConditionTest()
    {
        $this->createMockCondition();

        Cache::shouldReceive('has')
            ->once()
            ->andReturn(true);

        Cache::shouldReceive('get')
            ->once()
            ->andReturn(true);

        // true assertion based upon the return value of the mocked cache facade above.
        $this->assertTrue($this->endorsement->fresh()->conditionsMetForUser($this->user));
    }

    /** @test */
    public function itFlushesUserEndorsementCacheAfterATCSession()
    {
        $this->createMockCondition();

        $spy = Cache::spy();

        $this->assertFalse($this->endorsement->fresh()->conditionsMetForUser($this->user));

        $spy->shouldHaveReceived('put')
            ->once();

        $atc = factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'EGKK_TWR',
            'connected_at' => Carbon::now()->subHours(2),
        ]);
        $atc->disconnectAt(Carbon::now());

        $spy->shouldHaveReceived('forget')
            ->times(Endorsement::count());

        $this->assertTrue($this->endorsement->fresh()->conditionsMetForUser($this->user));
    }

    private function createMockCondition($positions = ['EGKK_%'], $type = Endorsement\Condition::TYPE_ON_SINGLE_AIRFIELD)
    {
        // create condition requiring an hour on a EGKK_TWR
        return factory(Endorsement\Condition::class)->create(
            [
                'endorsement_id' => $this->endorsement->id,
                'required_hours' => 1,
                'within_months' => null,
                'type' => $type,
                'positions' => $positions,
            ]
        );
    }
}
