<?php

namespace Tests\Feature\Account;

use App\Models\Sys\Notification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function testLoggedInUserTakenToCompulsoryNotification()
    {
        factory(Notification::class)->create(['status' => Notification::STATUS_MUST_ACKNOWLEDGE]);

        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard'))
            ->assertRedirect(route('mship.notification.list'));
    }

    /** @test */
    public function testLoggedInUserNotForcedToNonCompulsoryNotification()
    {
        factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);

        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard'))
            ->assertOk();
    }

    /** @test */
    public function testLoggedInUserCanReadNotification()
    {
        $notification = factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);

        $this->actingAs($this->user)
            ->post(route('mship.notification.acknowledge', $notification))
            ->assertRedirect(route('mship.notification.list'));
    }

    /** @test */
    public function testUserCantDoubleReadNotification()
    {
        $notification = factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);

        $this->user->readSystemNotifications()
            ->attach($notification->id, ['created_at' => Carbon::now()]);

        $this->actingAs($this->user)
            ->post(route('mship.notification.acknowledge', $notification))
            ->assertRedirect(route('mship.manage.dashboard'));
    }

    /** @test */
    public function testNotificationBannerDoesntShowOnSecondaryLogin()
    {
        factory(Notification::class)->create();
        $this->user->password = '123';
        $this->user->save();

        $this->followingRedirects()->actingAs($this->user, 'vatsim-sso')
            ->get(route('auth-secondary'))
            ->assertDontSee('You currently have unread notifications');

        $this->followingRedirects()->actingAs($this->user, 'web')
            ->get(route('mship.manage.dashboard'))
            ->assertSee('You currently have unread notifications');
    }
}
