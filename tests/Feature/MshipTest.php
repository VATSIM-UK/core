<?php

namespace Tests\Feature;

use App\Models\Sys\Notification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\BrowserKitTestCase;
use Tests\TestCase;

class MshipTest extends TestCase
{
    use DatabaseTransactions;
    
    public function testItLoadsSuccessfully()
    {
        $response = $this->get('/');
        $response->assertSuccessful();
    }

    public function testItRedirectsToDashboard()
    {
        Notification::query()->delete();
        $account = factory(\App\Models\Mship\Account::class)->create();

        $this->actingAs($account);

        $response = $this->get('/');
        $response->assertRedirect(route('mship.manage.dashboard'));
    }

    public function testMustReadNotificationsCauseRedirect()
    {
        Notification::query()->delete();
        $account = factory(\App\Models\Mship\Account::class)->create();
        factory(\App\Models\Sys\Notification::class, 'must_read')->create();

        $this->actingAs($account);

        $response = $this->get(route('mship.manage.dashboard'));
        $response->assertRedirect(route('mship.notification.list'));
    }

    public function testImportantNotificationsCauseRedirect()
    {
        Notification::query()->delete();
        $account = factory(\App\Models\Mship\Account::class)->create();
        factory(\App\Models\Sys\Notification::class, 'important')->create();

        $this->actingAs($account);

        $response = $this->get(route('mship.manage.dashboard'));
        $response->assertRedirect(route('mship.notification.list'));
    }

    public function testGeneralNotificationsDoNotCauseRedirect()
    {
        Notification::query()->delete();
        $account = factory(\App\Models\Mship\Account::class)->create();
        factory(\App\Models\Sys\Notification::class)->create();

        $this->actingAs($account);

        $response = $this->get(route('mship.manage.dashboard'));
        $response->assertSuccessful();
    }
}
