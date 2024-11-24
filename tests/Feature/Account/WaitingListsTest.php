<?php

namespace Tests\Feature\Account;

use App\Events\Mship\AccountAltered;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WaitingListsTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        // disable account altered event to stop it from removing accounts from waiting lists
        Event::fake([AccountAltered::class]);

        $this->actingAs($this->privacc);
    }

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
}
