<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Atc\Endorsement\Condition;
use App\Models\NetworkData\Atc;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListFlag;
use App\Services\Training\CheckWaitingListEligibility;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaitingListCheckEligibilityServiceTest extends TestCase
{
    use DatabaseTransactions;

    private WaitingList $waitingList;

    public function setUp(): void
    {
        parent::setUp();

        $this->waitingList = factory(WaitingList::class)->create();

        $this->actingAs($this->privacc);
    }

    public function test_returns_false_if_basic_12_hour_check_fails()
    {
        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'minutes_online' => 60,
            'disconnected_at' => now(),
        ]);

        $result = (new CheckWaitingListEligibility($this->user))->checkBaseControllingHours($this->waitingList);

        $this->assertFalse($result);
    }

    public function test_returns_true_if_basic_12_hour_check_passes()
    {
        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'minutes_online' => 721,
            'disconnected_at' => now(),
        ]);

        $result = (new CheckWaitingListEligibility($this->user))->checkBaseControllingHours($this->waitingList);

        $this->assertTrue($result);
    }

    public function test_returns_true_when_waiting_list_has_no_flags()
    {
        $waitingList = factory(WaitingList::class)->create();
        $waitingList->addToWaitingList($this->user, $this->privacc);

        $result = (new CheckWaitingListEligibility($this->user))->checkWaitingListFlags($waitingList->fresh());

        $this->assertTrue($result['overall']);
        $this->assertNull($result['summary']);
    }

    public function test_passes_checks_when_manual_flag_is_true_in_all_flags_config()
    {
        $waitingList = factory(WaitingList::class)->create();
        $waitingList->addToWaitingList($this->user, $this->privacc);

        $flag = factory(WaitingListFlag::class)->create([
            'name' => 'manual',
            'list_id' => $waitingList->id,
            'default_value' => false,
        ]);
        $waitingList->addFlag($flag);
        $waitingList->accounts()->first()->pivot->markFlag($flag);

        $result = (new CheckWaitingListEligibility($this->user))->checkWaitingListFlags($waitingList->fresh());

        $this->assertTrue($result['overall']);
        $this->assertEquals($result['summary'], [$flag->name => true]);
    }

    public function test_fails_checks_when_manual_flag_is_false_in_all_flags_config()
    {
        $waitingList = factory(WaitingList::class)->create();
        $waitingList->addToWaitingList($this->user, $this->privacc);

        $flag = $this->createFlag('manual', $waitingList, false);

        $result = (new CheckWaitingListEligibility($this->user))->checkWaitingListFlags($waitingList);

        $this->assertFalse($result['overall']);
        $this->assertEquals($result['summary'], [$flag->name => false]);
    }

    public function test_fails_check_when_endorsement_linked_flag_is_false()
    {
        $waitingList = factory(WaitingList::class)->create();
        $waitingList->addToWaitingList($this->user, $this->privacc);

        factory(Atc::class)->create(['account_id' => $this->user->id, 'callsign' => 'EGGD_APP', 'minutes_online' => 35]);
        $condition = factory(Condition::class)->create(['required_hours' => 1, 'positions' => ['EGGD_APP']]);

        $flag = factory(WaitingListFlag::class)->create([
            'name' => 'endorsement',
            'list_id' => $waitingList->id,
            'default_value' => false,
            'endorsement_id' => $condition->endorsement->id,
        ]);
        $waitingList->addFlag($flag);
        $waitingList->fresh();

        $result = (new CheckWaitingListEligibility($this->user))->checkWaitingListFlags($waitingList->fresh());

        $this->assertFalse($result['overall']);
        $this->assertEquals($result['summary'], [$flag->name => false]);
    }

    public function test_pass_check_when_endorsement_linked_flag_is_true()
    {
        $waitingList = factory(WaitingList::class)->create();
        $waitingList->addToWaitingList($this->user, $this->privacc);

        factory(Atc::class)->create(['account_id' => $this->user->id, 'callsign' => 'EGGD_APP', 'minutes_online' => 65]);
        $condition = factory(Condition::class)->create(['required_hours' => 1, 'positions' => ['EGGD_APP']]);

        $flag = factory(WaitingListFlag::class)->create([
            'name' => 'endorsement',
            'list_id' => $waitingList->id,
            'default_value' => false,
            'endorsement_id' => $condition->endorsement->id,
        ]);
        $waitingList->addFlag($flag);
        $waitingList->fresh();

        $result = (new CheckWaitingListEligibility($this->user))->checkWaitingListFlags($waitingList->fresh());

        $this->assertTrue($result['overall']);
        $this->assertEquals($result['summary'], [$flag->name => true]);
    }

    public function test_pass_check_on_any_with_failing_and_passing_flags()
    {
        $waitingList = factory(WaitingList::class)->create();
        $waitingList->flags_check = WaitingList::ANY_FLAGS;
        $waitingList->save();
        $waitingList->addToWaitingList($this->user, $this->privacc);

        $flag1 = $this->createFlag('manual', $waitingList, false);
        $flag2 = $this->createFlag('endorsement', $waitingList, false);

        factory(Atc::class)->create(['account_id' => $this->user->id, 'callsign' => 'EGGD_APP', 'minutes_online' => 65]);
        $condition = factory(Condition::class)->create(['required_hours' => 1, 'positions' => ['EGGD_APP']]);

        $flag3 = factory(WaitingListFlag::class)->create([
            'name' => 'endorsement_2',
            'list_id' => $waitingList->id,
            'endorsement_id' => $condition->endorsement->id,
        ]);
        $waitingList->addFlag($flag3);
        $waitingList->fresh();

        $result = (new CheckWaitingListEligibility($this->user))->checkWaitingListFlags($waitingList->fresh());

        $this->assertTrue($result['overall']);
        $this->assertEquals($result['summary'], [$flag1->name => false, $flag2->name => false, $flag3->name => true]);
    }

    public function test_pass_check_on_all_with_all_passing_automated_flags()
    {
        $waitingList = factory(WaitingList::class)->create();
        $waitingList->addToWaitingList($this->user, $this->privacc);

        factory(Atc::class)->create(['account_id' => $this->user->id, 'callsign' => 'EGGD_APP', 'minutes_online' => 65]);
        factory(Atc::class)->create(['account_id' => $this->user->id, 'callsign' => 'EGNX_APP', 'minutes_online' => 65]);
        $condition = factory(Condition::class)->create(['required_hours' => 1, 'positions' => ['EGGD_APP']]);
        $conditionSecond = factory(Condition::class)->create(['required_hours' => 1, 'positions' => ['EGNX_APP']]);

        $flag1 = factory(WaitingListFlag::class)->create([
            'name' => 'endorsement',
            'list_id' => $waitingList->id,
            'default_value' => false,
            'endorsement_id' => $condition->endorsement->id,
        ]);
        $flag2 = factory(WaitingListFlag::class)->create([
            'name' => 'endorsement',
            'list_id' => $waitingList->id,
            'default_value' => false,
            'endorsement_id' => $conditionSecond->endorsement->id,
        ]);
        $waitingList->addFlag($flag1);
        $waitingList->addFlag($flag2);
        $waitingList->fresh();

        $result = (new CheckWaitingListEligibility($this->user))->checkWaitingListFlags($waitingList->fresh(), 'all');

        $this->assertTrue($result['overall']);
        $this->assertEquals($result['summary'], [$flag1->name => true, $flag2->name => true]);
    }

    public function test_hour_check_is_true_when_feature_toggle_is_false()
    {
        $waitingList = factory(WaitingList::class)->create();
        $waitingList->feature_toggles = ['check_atc_hours' => false];

        $waitingList->addToWaitingList($this->user, $this->privacc);

        $service = new CheckWaitingListEligibility($this->user);

        $this->assertTrue($service->checkBaseControllingHours($waitingList));
    }

    private function createFlag($name, $waitingList, $defaultValue = false)
    {
        $flag = factory(WaitingListFlag::class)->create([
            'name' => $name,
            'list_id' => $waitingList->id,
            'default_value' => $defaultValue,
        ]);
        $waitingList->addFlag($flag);
        $waitingList->fresh();

        return $flag;
    }
}
