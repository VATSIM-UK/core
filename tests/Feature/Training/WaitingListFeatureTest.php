<?php

namespace Tests\Feature\Training;

use App\Models\Mship\Account;
use App\Models\Mship\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Training\WaitingList;
use Tests\TestCase;

class WaitingListFeatureTest extends TestCase
{
    use DatabaseTransactions;

    private $waitingList;
    private $staffAccount;

    public function setUp()
    {
        parent::setUp();

        $this->waitingList = factory(WaitingList::class)->create();

        $this->staffAccount = factory(Account::class)->create();

        $this->staffAccount->roles()->attach(Role::find(1));
    }

    /** @test **/
    public function testStudentCanBeAddedToWaitingList()
    {
        $account = factory(Account::class)->create();

        $this->actingAs($this->staffAccount)->post(route('training.waitingList.store', $this->waitingList), [
            'account_id' => $account->id,
        ])->assertRedirect(route('training.waitingList.show', $this->waitingList));
    }
}
