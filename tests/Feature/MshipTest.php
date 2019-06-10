<?php

namespace Tests\Feature;

use App\Models\Sys\Notification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MshipTest extends TestCase
{
    use DatabaseTransactions;

    public function testItLoadsSuccessfully()
    {
        $this->get('/')
            ->assertSuccessful();
    }

    public function testItRedirectsToDashboard()
    {
        Notification::query()->delete();

        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertRedirect(route('mship.manage.dashboard'));
    }
}
