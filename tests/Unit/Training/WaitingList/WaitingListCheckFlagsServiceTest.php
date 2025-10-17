<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Atc\PositionGroupCondition;
use App\Models\NetworkData\Atc;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListFlag;
use App\Services\Training\CheckWaitingListFlags;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaitingListCheckFlagsServiceTest extends TestCase
{
    use DatabaseTransactions;

    private WaitingList $waitingList;

    protected function setUp(): void
    {
        parent::setUp();

        $this->waitingList = WaitingList::factory()->create();

        $this->actingAs($this->privacc);
    }

    public function test_passes_checks_when_manual_flag_is_true_in_all_flags_config()
    {
        $waitingList = WaitingList::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($this->user, $this->privacc);

        $flag = WaitingListFlag::factory()->create([
            'name' => 'manual',
            'list_id' => $waitingList->id,
            'default_value' => false,
        ]);
        $waitingList->addFlag($flag);
        $waitingListAccount->markFlag($flag);

        $result = (new CheckWaitingListFlags($this->user))->checkWaitingListFlags($waitingList->fresh());

        $this->assertEquals([$flag->name => true], $result['summary']);
    }

    public function test_fails_checks_when_manual_flag_is_false_in_all_flags_config()
    {
        $waitingList = WaitingList::factory()->create();
        $waitingList->addToWaitingList($this->user, $this->privacc);

        $flag = $this->createFlag('manual', $waitingList, false);

        $result = (new CheckWaitingListFlags($this->user))->checkWaitingListFlags($waitingList);

        $this->assertEquals([$flag->name => false], $result['summary']);
    }

    public function test_fails_check_when_endorsement_linked_flag_is_false()
    {
        $waitingList = WaitingList::factory()->create();
        $waitingList->addToWaitingList($this->user, $this->privacc);

        factory(Atc::class)->create(['account_id' => $this->user->id, 'callsign' => 'EGGD_APP', 'minutes_online' => 35]);
        $condition = factory(PositionGroupCondition::class)->create(['required_hours' => 1, 'positions' => ['EGGD_APP']]);

        $flag = WaitingListFlag::factory()->create([
            'name' => 'endorsement',
            'list_id' => $waitingList->id,
            'default_value' => false,
            'position_group_id' => $condition->positionGroup->id,
        ]);
        $waitingList->addFlag($flag);
        $waitingList->fresh();

        $result = (new CheckWaitingListFlags($this->user))->checkWaitingListFlags($waitingList->fresh());

        $this->assertEquals([$flag->name => false], $result['summary']);
    }

    public function test_pass_check_when_endorsement_linked_flag_is_true()
    {
        $waitingList = WaitingList::factory()->create();
        $waitingList->addToWaitingList($this->user, $this->privacc);

        factory(Atc::class)->create(['account_id' => $this->user->id, 'callsign' => 'EGGD_APP', 'minutes_online' => 65]);
        $condition = factory(PositionGroupCondition::class)->create(['required_hours' => 1, 'positions' => ['EGGD_APP']]);

        $flag = WaitingListFlag::factory()->create([
            'name' => 'endorsement',
            'list_id' => $waitingList->id,
            'default_value' => false,
            'position_group_id' => $condition->positionGroup->id,
        ]);
        $waitingList->addFlag($flag);
        $waitingList->fresh();

        $result = (new CheckWaitingListFlags($this->user))->checkWaitingListFlags($waitingList->fresh());

        $this->assertEquals([$flag->name => true], $result['summary']);
    }

    public function test_pass_check_on_all_with_all_passing_automated_flags()
    {
        $waitingList = WaitingList::factory()->create();
        $waitingList->addToWaitingList($this->user, $this->privacc);

        factory(Atc::class)->create(['account_id' => $this->user->id, 'callsign' => 'EGGD_APP', 'minutes_online' => 65]);
        factory(Atc::class)->create(['account_id' => $this->user->id, 'callsign' => 'EGNX_APP', 'minutes_online' => 65]);
        $condition = factory(PositionGroupCondition::class)->create(['required_hours' => 1, 'positions' => ['EGGD_APP']]);
        $conditionSecond = factory(PositionGroupCondition::class)->create(['required_hours' => 1, 'positions' => ['EGNX_APP']]);

        $flag1 = WaitingListFlag::factory()->create([
            'name' => 'endorsement',
            'list_id' => $waitingList->id,
            'default_value' => false,
            'position_group_id' => $condition->positionGroup->id,
        ]);
        $flag2 = WaitingListFlag::factory()->create([
            'name' => 'endorsement',
            'list_id' => $waitingList->id,
            'default_value' => false,
            'position_group_id' => $conditionSecond->positionGroup->id,
        ]);
        $waitingList->addFlag($flag1);
        $waitingList->addFlag($flag2);
        $waitingList->fresh();

        $result = (new CheckWaitingListFlags($this->user))->checkWaitingListFlags($waitingList->fresh(), 'all');

        $this->assertEquals($result['summary'], [$flag1->name => true, $flag2->name => true]);
    }

    private function createFlag($name, $waitingList, $defaultValue = false)
    {
        $flag = WaitingListFlag::factory()->create([
            'name' => $name,
            'list_id' => $waitingList->id,
            'default_value' => $defaultValue,
        ]);
        $waitingList->addFlag($flag);
        $waitingList->fresh();

        return $flag;
    }
}
