<?php

namespace Tests\Unit\Mship;

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
    public function itCanAssociateReadNotifications()
    {
        $notification = factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);
        $this->assertCount(0, $this->account->readSystemNotifications);
        $this->account->readSystemNotifications()
            ->attach($notification->id, ['created_at' => Carbon::now()]);
        $this->assertCount(1, $this->account->fresh()->readSystemNotifications);
    }

    /** @test * */
    public function itCanReportANotificationIsRead()
    {
        $notification1 = factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);
        $notification2 = factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);
        $this->account->readSystemNotifications()
            ->attach($notification1->id, ['created_at' => Carbon::now()]);
        $this->assertTrue($this->account->hasReadNotification($notification1));
        $this->assertFalse($this->account->hasReadNotification($notification2));
    }

    /** @test * */
    public function itCanShowsUnreadNotifications()
    {
        factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);
        $this->assertCount(1, $this->account->unread_notifications);
    }
}
