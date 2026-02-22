<?php

namespace Tests\Feature\Account;

use App\Models\Sys\Notification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_update_community_display_settings()
    {
        $this->actingAs($this->user)
            ->post(route('mship.manage.community-display-settings.post'), ['hide_real_name_in_community' => '1'])
            ->assertRedirect(route('mship.manage.dashboard'));

        $this->assertTrue($this->user->fresh()->hide_real_name_in_community);
    }

    public function test_it_redirects_to_dashboard()
    {
        Notification::query()->delete();

        $this->actingAs($this->user)
            ->get(route('landing'))
            ->assertRedirect(route('mship.manage.dashboard'));
    }
}
