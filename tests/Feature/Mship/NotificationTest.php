<?php

namespace Tests\Feature\Mship;

use App\Models\Mship\Account;
use App\Models\Sys\Notification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use DatabaseTransactions;

    private $account;

    protected function setUp()
    {
        parent::setUp();
        $this->account = factory(Account::class)->create();
    }

    /** @test * */
    public function testLoggedInUserTakenToCompulsoryNotification()
    {
        factory(Notification::class)->create(['status' => Notification::STATUS_MUST_ACKNOWLEDGE]);
        $this->actingAs($this->account)->get(route('mship.manage.dashboard'))->assertRedirect(route('mship.notification.list'));
    }

    /** @test * */
    public function testLoggedInUserNotForcedToNonCompulsoryNotification()
    {
        factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);
        $this->actingAs($this->account)->get(route('mship.manage.dashboard'))->assertOk();
    }

    /** @test * */
    public function testLoggedInUserCanReadNotification()
    {
        $notification = factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);
        $this->actingAs($this->account)->post(route('mship.notification.acknowledge', $notification))->assertRedirect(route('mship.notification.list'));
    }

    /** @test * */
    public function testUserCantDoubleReadNotification()
    {
        $notification = factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);
        $this->account->readSystemNotifications()
            ->attach($notification->id, ['created_at' => Carbon::now()]);
        $this->actingAs($this->account)->post(route('mship.notification.acknowledge', $notification))->assertRedirect(route('mship.manage.dashboard'));
    }

    /** @test * */
    public function testNotificationBannerDoesntShowOnSecondaryLogin()
    {
        factory(Notification::class)->create();
        $this->account->password = "123";
        $this->account->save();
        $this->followingRedirects()->actingAs($this->account, 'vatsim-sso')->get(route('auth-secondary'))->assertDontSee("You currently have unread notifications");
        $this->followingRedirects()->actingAs($this->account, 'web')->get(route('dashboard'))->assertSee("You currently have unread notifications");
    }
}
