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

    public function test_returns_false_if_basic_12_hour_check_fails()
    {
        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'minutes_online' => 60,
            'disconnected_at' => now()
        ]);

        $result = (new CheckWaitingListEligibility($this->user))->checkBaseControllingHours();

        $this->assertFalse($result);
    }

    public function test_returns_true_if_basic_12_hour_check_passes()
    {
        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'minutes_online' => 721,
            'disconnected_at' => now()
        ]);

        $result = (new CheckWaitingListEligibility($this->user))->checkBaseControllingHours();

        $this->assertTrue($result);
    }

    public function test_returns_true_when_waiting_list_has_no_flags()
    {
        $waitingList = factory(WaitingList::class)->create();
        $waitingList->addToWaitingList($this->user, $this->privacc);


        [$result, $summary] = (new CheckWaitingListEligibility($this->user))->checkWaitingListFlags($waitingList->fresh());

        $this->assertTrue($result);
        $this->assertNull($summary);
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

        [$result, $summary] = (new CheckWaitingListEligibility($this->user))->checkWaitingListFlags($waitingList->fresh());

        $this->assertTrue($result);
        $this->assertEquals($summary, [$flag->id => true]);
    }

    public function test_fails_checks_when_manual_flag_is_false_in_all_flags_config()
    {
        $waitingList = factory(WaitingList::class)->create();
        $waitingList->addToWaitingList($this->user, $this->privacc);

        $flag = $this->createFlag('manual', $waitingList, false);

        [$result, $summary] = (new CheckWaitingListEligibility($this->user))->checkWaitingListFlags($waitingList);

        $this->assertFalse($result);
        $this->assertEquals($summary, [$flag->id => false]);
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
            'endorsement_id' => $condition->endorsement->id
        ]);
        $waitingList->addFlag($flag);
        $waitingList->fresh();

        [$result, $summary] = (new CheckWaitingListEligibility($this->user))->checkWaitingListFlags($waitingList->fresh());

        $this->assertFalse($result);
        $this->assertEquals($summary, [$flag->id => false]);
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
            'endorsement_id' => $condition->endorsement->id
        ]);
        $waitingList->addFlag($flag);
        $waitingList->fresh();

        [$result, $summary] = (new CheckWaitingListEligibility($this->user))->checkWaitingListFlags($waitingList->fresh());

        $this->assertTrue($result);
        $this->assertEquals($summary, [$flag->id => true]);
    }

    public function test_pass_check_on_any_with_failing_and_passing_flags()
    {
        $waitingList = factory(WaitingList::class)->create();
        $waitingList->addToWaitingList($this->user, $this->privacc);

        $flag1 = $this->createFlag('manual', $waitingList, false);
        $flag2 = $this->createFlag('endorsement', $waitingList, false);

        factory(Atc::class)->create(['account_id' => $this->user->id, 'callsign' => 'EGGD_APP', 'minutes_online' => 65]);
        $condition = factory(Condition::class)->create(['required_hours' => 1, 'positions' => ['EGGD_APP']]);

        $flag3 = factory(WaitingListFlag::class)->create([
            'name' => 'endorsement',
            'list_id' => $waitingList->id,
            'default_value' => false,
            'endorsement_id' => $condition->endorsement->id
        ]);
        $waitingList->addFlag($flag3);
        $waitingList->fresh();

        [$result, $summary] = (new CheckWaitingListEligibility($this->user))->checkWaitingListFlags($waitingList->fresh());

        $this->assertTrue($result);
        $this->assertEquals($summary, [$flag1->id => false, $flag2->id => false, $flag3->id => true]);
    }

    public function test_pass_check_on_all_with_all_passing_automated_flags()
    {

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
