<?php

namespace Tests\Unit\Endorsements;

use App\Models\Atc\PositionGroup;
use App\Models\Atc\PositionGroupCondition;
use App\Models\Mship\Qualification;
use App\Models\NetworkData\Atc;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConditionModelTest extends TestCase
{
    use DatabaseTransactions;

    private $condition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condition = factory(PositionGroupCondition::class)->create();
    }

    #[Test]
    public function it_can_be_created()
    {
        $condition = PositionGroupCondition::create([
            'position_group_id' => 100,
            'positions' => ['EGLL_TWR', 'EGPH_%'],
            'required_hours' => 10,
            'type' => PositionGroupCondition::TYPE_SUM_OF_AIRFIELDS,
            'within_months' => null,
        ]);

        $this->assertDatabaseHas('position_group_conditions', [
            'id' => $condition->id,
            'position_group_id' => 100,
            'positions' => json_encode(['EGLL_TWR', 'EGPH_%']),
            'required_hours' => 10,
            'type' => PositionGroupCondition::TYPE_SUM_OF_AIRFIELDS,
            'within_months' => null,
        ]);
    }

    #[Test]
    public function it_returns_an_array_of_positions()
    {
        $this->assertTrue(is_array($this->condition->positions));
    }

    #[Test]
    public function it_returns_a_list_of_human_positions()
    {
        $this->condition->positions = ['EGKK_%', 'EGLL_%'];
        $this->condition->save();

        $this->assertTrue(is_array($this->condition->fresh()->human_positions));
        $this->assertEquals(['EGKK_XXX', 'EGLL_XXX'], $this->condition->fresh()->human_positions);
    }

    #[Test]
    public function it_can_be_associated_with_a_endorsement()
    {
        $positionGroup = factory(PositionGroup::class)->create();
        $condition = factory(PositionGroupCondition::class)->make(['position_group_id' => null]);

        $this->assertNull($condition->positionGroup);
        $condition->positionGroup()->associate($positionGroup);
        $condition->save();

        $this->assertEquals($positionGroup->id, $condition->fresh()->positionGroup->id);
    }

    #[Test]
    public function it_correctly_reports_progress()
    {
        $condition = factory(PositionGroupCondition::class)->make(['positions' => ['EGLL_%', 'ESSEX_APP'], 'required_hours' => 10, 'within_months' => 2, 'type' => PositionGroupCondition::TYPE_ON_SINGLE_AIRFIELD]);

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

    #[Test]
    public function it_correctly_reports_met_and_progress_for_single_airfield()
    {
        $condition = factory(PositionGroupCondition::class)->create(['positions' => ['EGLL_%', 'ESSEX_APP'], 'required_hours' => 10, 'within_months' => 2, 'type' => PositionGroupCondition::TYPE_ON_SINGLE_AIRFIELD]);

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
        $this->assertEquals(5, $condition->overallProgressForUser($this->user));

        $session->minutes_online = 10 * 60;
        $session->save();
        $this->assertTrue($condition->fresh()->isMetForUser($this->user));
        $this->assertEquals(10, $condition->fresh()->overallProgressForUser($this->user));
    }

    #[Test]
    public function it_correctly_reports_met_and_progress_for_sum()
    {
        $condition = factory(PositionGroupCondition::class)->create(['positions' => ['EGLL_%', 'ESSEX_APP'], 'required_hours' => 10, 'within_months' => 2, 'type' => PositionGroupCondition::TYPE_SUM_OF_AIRFIELDS]);

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
        $this->assertEquals(9, $condition->overallProgressForUser($this->user));

        $session->minutes_online = 5 * 60;
        $session->save();
        $this->assertTrue($condition->fresh()->isMetForUser($this->user));
        $this->assertEquals(10, $condition->fresh()->overallProgressForUser($this->user));
    }

    #[Test]
    public function it_correctly_checks_for_qualification_when_present()
    {
        $requiredQualification = Qualification::code('S3')->get()->first()->id;

        $condition = factory(PositionGroupCondition::class)->create([
            'positions' => ['ESSEX_APP'],
            'required_hours' => 10,
            'within_months' => 2,
            'type' => PositionGroupCondition::TYPE_ON_SINGLE_AIRFIELD,
            'required_qualification' => $requiredQualification,
        ]);

        // create a session short of the requirement at the rating required
        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'ESSEX_APP',
            'minutes_online' => 9 * 60, // 9 hours
            'connected_at' => Carbon::now()->subMonths(1),
            'disconnected_at' => Carbon::now()->subMonths(1),
            'qualification_id' => $requiredQualification,
        ]);

        // create a session which would meet the hours criteria, but not at the right rating
        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'ESSEX_APP',
            'minutes_online' => 4 * 60, // 4 hours
            'connected_at' => Carbon::now()->subMonths(1),
            'disconnected_at' => Carbon::now()->subMonths(1),
            'qualification_id' => Qualification::code('S2')->get()->first()->id,
        ]);

        $this->assertFalse($condition->fresh()->isMetForUser($this->user));

        // create a session to meet requirement at the rating required
        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'ESSEX_APP',
            'minutes_online' => 3 * 60, // 3 hours
            'connected_at' => Carbon::now()->subMonths(1),
            'disconnected_at' => Carbon::now()->subMonths(1),
            'qualification_id' => $requiredQualification,
        ]);

        $this->assertTrue($condition->fresh()->isMetForUser($this->user));
    }
}
