<?php

namespace Tests\Unit\Endorsements;

use App\Models\Atc\PositionGroup;
use App\Models\Atc\PositionGroupCondition;
use App\Models\NetworkData\Atc;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_can_be_created()
    {
        $positionGroup = PositionGroup::create([
            'name' => 'My First Endorsement',
        ]);

        $this->assertDatabaseHas('position_groups', ['id' => $positionGroup->id, 'name' => $positionGroup->name]);
    }

    #[Test]
    public function it_can_be_associated_with_a_condition()
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

    #[Test]
    public function it_will_report_conditions_met_when_all_are_met_for_single_airfield()
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

    #[Test]
    public function it_will_report_conditions_not_met_when_network_data_not_present_for_single_airfield()
    {
        $this->createMockCondition();

        // network data not present for the specified position
        $this->assertFalse($this->positionGroup->fresh()->conditionsMetForUser($this->user));
    }

    #[Test]
    public function it_will_report_conditions_met_for_multiple_airfields_when_network_data_present()
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

    #[Test]
    public function it_will_report_conditions_not_met_for_multiple_airfields_when_required_time_isnt_met()
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

    #[Test]
    public function it_should_return_value_from_cache_during_condition_test()
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
