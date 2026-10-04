<?php

namespace Tests\Feature\Roster;

use App\Livewire\Roster\Search;
use App\Models\Mship\Account;
use Livewire\Livewire;
use Tests\TestCase;

class SearchTest extends TestCase
{
    public function test_renders_search_form(): void
    {
        $account = Account::factory()->create();

        Livewire::actingAs($account)
            ->test(Search::class)
            ->assertSee('Find a controller')
            ->assertSee('VATSIM CID');
    }

    public function test_redirects_to_show_for_known_cid(): void
    {
        $account = Account::factory()->create();
        $target = Account::factory()->create();

        Livewire::actingAs($account)
            ->test(Search::class)
            ->set('searchTerm', (string) $target->id)
            ->call('search')
            ->assertRedirect(route('site.roster.show', ['account' => $target]));
    }

    public function test_notifies_for_unknown_cid(): void
    {
        $account = Account::factory()->create();

        Livewire::actingAs($account)
            ->test(Search::class)
            ->set('searchTerm', '999999999')
            ->call('search')
            ->assertNotified('No account found with that CID.')
            ->assertSet('searchTerm', null);
    }
}
