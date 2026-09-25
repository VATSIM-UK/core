<?php

namespace Tests\Feature\Roster;

use App\Livewire\Roster\Index;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\Roster;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    public function test_shows_active_state_and_view_roster_action_when_on_roster(): void
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $account->addQualification(Qualification::code('S2')->first());

        Roster::create(['account_id' => $account->id]);

        Livewire::actingAs($account)
            ->test(Index::class)
            ->assertSee('Active')
            ->assertSee('View my roster')
            ->assertDontSee('Renew my currency');
    }

    public function test_shows_renew_action_when_inactive_but_eligible(): void
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $account->addQualification(Qualification::code('S2')->first());

        Livewire::actingAs($account)
            ->test(Index::class)
            ->assertSee('Inactive')
            ->assertSee('Renew my currency')
            ->assertDontSee('View my roster');
    }

    public function test_shows_contact_community_when_not_eligible(): void
    {
        $account = Account::factory()->create();

        Livewire::actingAs($account)
            ->test(Index::class)
            ->assertSee('contact Community')
            ->assertDontSee('Renew my currency')
            ->assertDontSee('View my roster');
    }

    public function test_always_shows_search_action(): void
    {
        $account = Account::factory()->create();

        Livewire::actingAs($account)
            ->test(Index::class)
            ->assertSee('Search the roster');
    }

    public function test_renders_success_flash_message(): void
    {
        $account = Account::factory()->create();
        $this->withSession(['success' => 'You have been reactivated.']);

        Livewire::actingAs($account)
            ->test(Index::class)
            ->assertSee('You have been reactivated.');
    }

    public function test_renders_error_flash_message(): void
    {
        $account = Account::factory()->create();
        $this->withSession(['error' => 'You are already on the roster!']);

        Livewire::actingAs($account)
            ->test(Index::class)
            ->assertSee('You are already on the roster!');
    }
}
