<?php

namespace Tests\Feature\Training;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WaitingListStatusFeatureTest extends TestCase
{
    private $waitingList;
    private $staffAccount;

    protected function setUp()
    {
        parent::setUp();

        $this->waitingList = factory(WaitingList::class)->create();

        $this->staffAccount = factory(Account::class)->create();

        $this->staffAccount->roles()->attach(Role::find(1));
    }

    /** @test **/
    public function testAStatusIsAssignedByDefaultOnCreate()
    {

    }
}
