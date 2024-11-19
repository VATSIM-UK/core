<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListFlag;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaitingListTest extends TestCase
{
    use DatabaseTransactions, WaitingListTestHelper;

    private WaitingList $waitingList;

    private $staffUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->waitingList = $this->createList();

        $this->actingAs($this->privacc);
    }

    /** @test * */
    public function itDisplaysNameOnToString()
    {
        $this->assertEquals($this->waitingList->name, $this->waitingList);
    }

    /** @test * */
    public function itHasAName()
    {
        $this->assertNotNull($this->waitingList->name);
        $this->assertNotNull($this->waitingList->slug);
    }

    /** @test * */
    public function itDetectsIfAtcList()
    {
        $atcList = factory(WaitingList::class)->create(['department' => 'atc']);

        $this->assertTrue($atcList->isAtcList());
        $this->assertFalse($atcList->isPilotList());
    }

    /** @test * */
    public function itDetectsIfPilotList()
    {
        $atcList = factory(WaitingList::class)->create(['department' => 'pilot']);

        $this->assertTrue($atcList->isPilotList());
        $this->assertFalse($atcList->isAtcList());
    }

    /** @test * */
    public function itCanHaveStudents()
    {
        $account = Account::factory()->make();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertCount(1, $this->waitingList->waitingListAccounts);

        $this->assertDatabaseHas('training_waiting_list_account',
            ['account_id' => $account->id, 'list_id' => $this->waitingList->id]);
    }

    /** @test */
    public function itCanFindPositionOfAccount()
    {
        $accounts_added_at = [Carbon::now()->subDays(10), Carbon::now()->subDays(1), Carbon::now()->subDays(4)];
        $accounts = [];
        foreach ($accounts_added_at as $date) {
            $accounts[] = Account::factory()->create();
        }

        $this->waitingList->department = WaitingList::PILOT_DEPARTMENT;
        $this->waitingList->save();
        $flag = $this->waitingList->addFlag(factory(WaitingListFlag::class)->create(['default_value' => false]));

        // Add to list
        foreach ($accounts as $i => $account) {
            $this->waitingList->addToWaitingList($account, $this->privacc, $accounts_added_at[$i]);
            WaitingList\WaitingListAccount::where('account_id', $account->id)->first()->markFlag($flag);
        }

        $this->waitingList = $this->waitingList->fresh();

        $findWaitingListAccount = function (Account $account) {
            return WaitingList\WaitingListAccount::whereAccountId($account->id)
                ->where('list_id', $this->waitingList->id)
                ->firstOrFail();
        };

        $this->assertEquals(1, $this->waitingList->positionOf($findWaitingListAccount($accounts[0]))); // First user is oldest, should be number 1
        $this->assertEquals(3, $this->waitingList->positionOf($findWaitingListAccount($accounts[1]))); // Second user is newest, should be number 3
        $this->assertEquals(2, $this->waitingList->positionOf($findWaitingListAccount($accounts[2])));
    }

    /** @test * */
    public function itCanRemoveUsers()
    {
        $account = Account::factory()->make();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertDatabaseHas('training_waiting_list_account',
            ['account_id' => $account->id, 'list_id' => $this->waitingList->id]);

        $this->waitingList->removeFromWaitingList($account);

        $this->assertDatabaseHas('training_waiting_list_account',
            [
                'account_id' => $account->id, 'list_id' => $this->waitingList->id, 'deleted_at' => now(),
            ]);
    }

    /** @test * */
    public function itUpdatesPositionsOnWaitingListRemoval()
    {
        $accounts = Account::factory(3)->create()->each(function ($account) {
            $this->waitingList->addToWaitingList($account, $this->privacc);
        });

        $this->waitingList->removeFromWaitingList($accounts[1]);

        $this->assertDatabaseHas('training_waiting_list_account', [
            'list_id' => $this->waitingList->id,
            'account_id' => $accounts[2]->id,
        ]);
    }

    /** @test */
    public function itCanHaveABooleanFlag()
    {
        $flag = factory(WaitingListFlag::class)->create();
        $this->waitingList->addFlag($flag);

        $this->assertTrue($this->waitingList->flags->contains($flag));
    }

    /** @test */
    public function itCanHaveFlagsRemoved()
    {
        $flag = factory(WaitingListFlag::class)->create();
        $this->waitingList->addFlag($flag);

        $this->waitingList->removeFlag($flag);

        $this->assertFalse($this->waitingList->flags->contains($flag));
    }

    /** @test */
    public function itCanDetectWhetherToShowAtcHourCheck()
    {
        $this->waitingList->department = WaitingList::ATC_DEPARTMENT;
        $this->waitingList->feature_toggles = null;
        $this->waitingList->save();

        // defaults to true in absence of feature toggle
        $this->assertTrue($this->waitingList->should_check_atc_hours);

        $this->waitingList->feature_toggles = ['check_atc_hours' => true];
        $this->waitingList->save();

        $this->assertTrue($this->waitingList->should_check_atc_hours);

        $this->waitingList->feature_toggles = ['check_atc_hours' => false];
        $this->waitingList->save();

        $this->assertFalse($this->waitingList->should_check_atc_hours);
    }

    /** @test */
    public function itCanDetectWhetherToCheckForCtsTheoryExam()
    {
        $this->waitingList->feature_toggles = null;
        $this->waitingList->save();

        // defaults to true in absence of feature toggle
        $this->assertTrue($this->waitingList->should_check_cts_theory_exam);

        $this->waitingList->feature_toggles = ['check_cts_theory_exam' => true];
        $this->waitingList->save();

        $this->assertTrue($this->waitingList->should_check_cts_theory_exam);

        $this->waitingList->feature_toggles = ['check_cts_theory_exam' => false];
        $this->waitingList->save();

        $this->assertFalse($this->waitingList->should_check_cts_theory_exam);
    }

    /** @test */
    public function itReturnsFeatureTogglesFormattedInArray()
    {
        $this->waitingList->feature_toggles = null;
        $this->waitingList->save();

        // check defaults when column not set.
        $this->assertEquals((object) ['check_atc_hours' => true, 'check_cts_theory_exam' => true], $this->waitingList->feature_toggles_formatted);

        $this->waitingList->feature_toggles = ['check_atc_hours' => true];
        $this->waitingList->save();

        // check_cts_theory_exam is not set, so it should default to true
        $this->assertEquals((object) ['check_atc_hours' => true, 'check_cts_theory_exam' => true], $this->waitingList->feature_toggles_formatted);

        $this->waitingList->feature_toggles = ['check_cts_theory_exam' => true];

        // check_atc_hours is not set, so it should default to true
        $this->assertEquals((object) ['check_atc_hours' => true, 'check_cts_theory_exam' => true], $this->waitingList->feature_toggles_formatted);

        $this->waitingList->feature_toggles = ['check_atc_hours' => false, 'check_cts_theory_exam' => false];
        $this->waitingList->save();

        // both values are false set so return value
        $this->assertEquals((object) ['check_atc_hours' => false, 'check_cts_theory_exam' => false], $this->waitingList->feature_toggles_formatted);
    }
}
