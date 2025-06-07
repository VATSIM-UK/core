<?php

namespace Tests\Feature\Account;

use App\Events\Mship\AccountAltered;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Roster;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function test_index_with_no_waiting_list_accounts()
    {
        factory(WaitingList::class)->create(['name' => 'My List']);

        $this->actingAs($this->user)
            ->get(route('mship.waiting-lists.index'))
            ->assertSee('You aren\'t in any waiting lists at the moment.', false)
            ->assertDontSee('My List');
    }

    #[Test]
    public function test_index_with_a_waiting_list_accounts()
    {
        $list = factory(WaitingList::class)->create(['name' => 'My List']);
        $list->addToWaitingList($this->user, $this->privacc);

        $this->actingAs($this->user)
            ->get(route('mship.waiting-lists.index'))
            ->assertSee('My List');
    }

    #[Test]
    public function test_does_not_show_on_roster_icon_when_not_configured_for_list()
    {
        $list = factory(WaitingList::class)->create(['name' => 'My List', 'feature_toggles' => ['display_on_roster' => false]]);
        $list->addToWaitingList($this->user, $this->privacc);

        $this->actingAs($this->user)
            ->get(route('mship.waiting-lists.index'))
            ->assertSee('My List')
            ->assertSee('N/A');
    }

    public function test_can_successfully_self_enrol_when_eligible_home_member_roster()
    {
        $account = Account::factory()->create();
        $list = factory(WaitingList::class)->create(['name' => 'My List', 'self_enrolment_enabled' => true, 'home_members_only' => true, 'requires_roster_membership' => true]);

        $account->addState(State::findByCode('DIVISION'));
        Roster::create(['account_id' => $account->id]);
        $account->refresh();

        $this->actingAs($account)
            ->get(route('mship.waiting-lists.index'))
            ->assertSee('My List')
            ->assertSee($list->name);

        $this->actingAs($account)
            ->post(route('mship.waiting-lists.self-enrol', $list->id))
            ->assertRedirect(route('mship.waiting-lists.index'))
            ->assertSessionHas('success', 'You have been added to the waiting list.');

        $this->assertTrue($list->includesAccount($account));
    }

    public function test_does_not_display_self_enrol_option_when_not_eligible()
    {
        $accountNotDivisionMember = Account::factory()->create();
        $accountNotDivisionMember->addState(State::findByCode('VISITING'));
        $accountNotDivisionMember->refresh();
        $list = factory(WaitingList::class)->create(['name' => 'My List', 'self_enrolment_enabled' => true, 'home_members_only' => true]);

        $this->actingAs($accountNotDivisionMember)
            ->get(route('mship.waiting-lists.index'))
            ->assertDontSee('My List')
            ->assertSee('You are not eligible to self-enrol on any waiting lists.');

        $this->actingAs($accountNotDivisionMember)
            ->post(route('mship.waiting-lists.self-enrol', $list->id))
            ->assertForbidden();
    }
}
