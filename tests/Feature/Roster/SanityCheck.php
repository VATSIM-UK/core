<?php

namespace Tests\Feature\Roster;

use App\Models\Mship\Account;
use Tests\TestCase;

class SanityCheck extends TestCase
{
    public function test_render_without_livewire_helpers(): void
    {
        $account = Account::factory()->create();
        $response = $this->actingAs($account)->get('/roster');

        $response->assertStatus(200);
        $response->assertSee("Hello, $account->name_first!");
    }
}
