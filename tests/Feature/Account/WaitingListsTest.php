<?php

namespace Tests\Feature\Account;

use App\Models\Training\WaitingList;
use App\Services\Training\AddToWaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaitingListsTests extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function testIndexWithNoWaitingListAccounts()
    {
        factory(WaitingList::class)->create(['name' => 'My List']);

        $this->actingAs($this->user)
            ->get(route('mship.waiting-lists.index'))
            ->assertSee('You aren\'t in any waiting lists at the moment.', false)
            ->assertDontSee('My List');
    }

    /** @test */
    public function testIndexWithAWaitingListAccounts()
    {
        $list = factory(WaitingList::class)->create(['name' => 'My List']);
        $list->addToWaitingList($this->user, $this->privacc);

        $this->actingAs($this->user)
            ->get(route('mship.waiting-lists.index'))
            ->assertSee('My List');
    }

    /** @test */
    public function testViewATCWaitingListDetailsNoFlags()
    {
        $list = factory(WaitingList::class)->create(['name' => 'My List']);
        handleService(new AddToWaitingList($list, $this->user, $this->privacc));

        $this->actingAs($this->user)
            ->get(route('mship.waiting-lists.view', ['waitingListId' => $list->id]))
            ->assertSee('My List')
            ->assertSee('Hour Check (Automatic)')
            ->assertSeeText('Have at least 12 hours on UK controller positions in the last 3 months')
            ->assertSeeText('0.0 / 12 hours');
    }

    /** @test */
    public function testViewPilotWaitingListDetailsNoFlags()
    {
        $list = factory(WaitingList::class)->create(['name' => 'My List', 'department' => WaitingList::PILOT_DEPARTMENT, 'enforce_hour_requirement' => 'no']);
        handleService(new AddToWaitingList($list, $this->user, $this->privacc));

        $response = $this->actingAs($this->user)
            ->get(route('mship.waiting-lists.view', ['waitingListId' => $list->id]))
            ->assertSee('My List')
            ->assertDontSee('Hour Check (Automatic)')
            ->assertDontSeeText('Have at least 12 hours on UK controller positions in the last 3 months');
    }
}
