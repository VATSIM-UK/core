<?php

namespace Tests\Feature\Account;

use App\Models\Sys\Notification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function test_logged_in_user_taken_to_compulsory_notification()
    {
        factory(Notification::class)->create(['status' => Notification::STATUS_MUST_ACKNOWLEDGE]);

        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard'))
            ->assertRedirect(route('mship.notification.list'));
    }

    #[Test]
    public function test_logged_in_user_not_forced_to_non_compulsory_notification()
    {
        factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);

        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard'))
            ->assertOk();
    }

    #[Test]
    public function test_logged_in_user_can_read_notification()
    {
        $notification = factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);

        $this->actingAs($this->user)
            ->post(route('mship.notification.acknowledge', $notification))
            ->assertRedirect(route('mship.notification.list'));
    }

    #[Test]
    public function test_user_cant_double_read_notification()
    {
        $notification = factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);

        $this->user->readSystemNotifications()
            ->attach($notification->id, ['created_at' => Carbon::now()]);

        $this->actingAs($this->user)
            ->post(route('mship.notification.acknowledge', $notification))
            ->assertRedirect(route('mship.manage.dashboard'));
    }

    #[Test]
    public function test_notification_content_is_escaped_and_preserves_line_breaks()
    {
        $notification = factory(Notification::class)->create([
            'content' => "Line one\n<script>alert(1)</script>",
        ]);

        $this->actingAs($this->user)
            ->get(route('mship.notification.list'))
            ->assertOk()
            ->assertSee('Line one<br />', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
