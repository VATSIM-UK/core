<?php

namespace Tests\Unit\Account\Notifications;

use App\Models\Sys\Notification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_associate_read_notifications()
    {
        $notification = factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);
        $this->assertCount(0, $this->user->readSystemNotifications);
        $this->user->readSystemNotifications()
            ->attach($notification->id, ['created_at' => Carbon::now()]);
        $this->assertCount(1, $this->user->fresh()->readSystemNotifications);
    }

    /** @test */
    public function it_can_report_a_notification_is_read()
    {
        $notification1 = factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);
        $notification2 = factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);
        $this->user->readSystemNotifications()
            ->attach($notification1->id, ['created_at' => Carbon::now()]);
        $this->assertTrue($this->user->hasReadNotification($notification1));
        $this->assertFalse($this->user->hasReadNotification($notification2));
    }

    /** @test */
    public function it_can_shows_unread_notifications()
    {
        factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);
        $this->assertCount(1, $this->user->unread_notifications);
    }
}
