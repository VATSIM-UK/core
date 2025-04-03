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

    protected function setUp(): void
    {
        parent::setUp();

        // disable account altered event to stop it from removing accounts from waiting lists
        Event::fake([AccountAltered::class]);

        $this->actingAs($this->privacc);
    }

    /** @test */
    public function test_index_with_no_waiting_list_accounts()
    {
        factory(WaitingList::class)->create(['name' => 'My List']);

        $this->actingAs($this->user)
            ->get(route('mship.waiting-lists.index'))
            ->assertSee('You aren\'t in any waiting lists at the moment.', false)
            ->assertDontSee('My List');
    }

    /** @test */
    public function test_index_with_a_waiting_list_accounts()
    {
        $list = factory(WaitingList::class)->create(['name' => 'My List']);
        $list->addToWaitingList($this->user, $this->privacc);

        $this->actingAs($this->user)
            ->get(route('mship.waiting-lists.index'))
            ->assertSee('My List');
    }

    /** @test */
    public function test_does_not_show_on_roster_icon_when_not_configured_for_list()
    {
        $list = factory(WaitingList::class)->create(['name' => 'My List', 'feature_toggles' => ["display_on_roster" => false]]);
        $list->addToWaitingList($this->user, $this->privacc);

        $this->actingAs($this->user)
            ->get(route('mship.waiting-lists.index'))
            ->assertSee('My List')
            ->assertSee('N/A');
    }
}
