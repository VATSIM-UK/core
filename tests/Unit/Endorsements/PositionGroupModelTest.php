<?php

namespace Tests\Unit\Endorsements;

use App\Models\Atc\PositionGroup;
use App\Models\Atc\PositionGroupCondition;
use App\Models\NetworkData\Atc;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PositionGroupModelTest extends TestCase
{
    use DatabaseTransactions;

    private $positionGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->positionGroup = factory(PositionGroup::class)->create();
    }

    /** @test */
    public function itCanBeCreated()
    {
        $positionGroup = PositionGroup::create([
            'name' => 'My First Endorsement',
        ]);

        $this->assertDatabaseHas('position_groups', ['id' => $positionGroup->id, 'name' => $positionGroup->name]);
    }

    /** @test */
    public function itCanBeAssociatedWithACondition()
    {
        $condition = factory(PositionGroupCondition::class)->make(['position_group_id' => null]);

        $this->assertCount(0, $this->positionGroup->fresh()->conditions);
        $this->positionGroup->conditions()->save($condition);
        $this->assertEquals($this->positionGroup->id, $condition->fresh()->position_group_id);
        $this->assertEquals($this->positionGroup->fresh()->conditions()->first()->id, $condition->id);
        $this->assertCount(1, $this->positionGroup->fresh()->conditions);

        $condition = factory(PositionGroupCondition::class)->make(['position_group_id' => null]);
        $this->positionGroup->conditions()->save($condition);
        $this->assertCount(2, $this->positionGroup->fresh()->conditions);
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

        $this->assertTrue($this->positionGroup->fresh()->conditionsMetForUser($this->user));
    }

    /** @test */
    public function itWillReportConditionsNotMetWhenNetworkDataNotPresentForSingleAirfield()
    {
        $this->createMockCondition();

        // network data not present for the specified position
        $this->assertFalse($this->positionGroup->fresh()->conditionsMetForUser($this->user));
    }

    /** @test */
    public function itWillReportConditionsMetForMultipleAirfieldsWhenNetworkDataPresent()
    {
        $this->createMockCondition(['EGKK_%', 'EGLL_%'], PositionGroupCondition::TYPE_SUM_OF_AIRFIELDS);

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
        $this->assertTrue($this->positionGroup->fresh()->conditionsMetForUser($this->user));
    }

    /** @test */
    public function itWillReportConditionsNotMetForMultipleAirfieldsWhenRequiredTimeIsntMet()
    {
        $this->createMockCondition(['EGKK_%', 'EGLL_%'], PositionGroupCondition::TYPE_SUM_OF_AIRFIELDS);

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

        $this->assertFalse($this->positionGroup->fresh()->conditionsMetForUser($this->user));
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
        $this->assertTrue($this->positionGroup->fresh()->conditionsMetForUser($this->user));
    }

    private function createMockCondition($positions = ['EGKK_%'], $type = PositionGroupCondition::TYPE_ON_SINGLE_AIRFIELD)
    {
        // create condition requiring an hour on a EGKK_TWR
        return factory(PositionGroupCondition::class)->create(
            [
                'position_group_id' => $this->positionGroup->id,
                'required_hours' => 1,
                'within_months' => null,
                'type' => $type,
                'positions' => $positions,
            ]
        );
    }
}
