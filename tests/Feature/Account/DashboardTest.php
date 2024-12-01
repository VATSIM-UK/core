<?php

namespace Tests\Feature\Account;

use App\Models\Sys\Notification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_redirects_to_dashboard()
    {
        Notification::query()->delete();

        $this->actingAs($this->user)
            ->get(route('landing'))
            ->assertRedirect(route('mship.manage.dashboard'));
    }
}
