<?php

namespace Tests\Unit\Listeners\Mship;

use App\Enums\EmailType;
use App\Listeners\Mship\CheckEmailPreferences;
use App\Models\Mship\Account;
use App\Notifications\Contracts\HasEmailType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckEmailPreferencesTest extends TestCase
{
    use DatabaseTransactions;

    private CheckEmailPreferences $listener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listener = new CheckEmailPreferences;
    }

    private function buildEvent(object $notifiable, Notification $notification): NotificationSending
    {
        return new NotificationSending(
            $notifiable,
            $notification,
            'mail'
        );
    }

    #[Test]
    public function it_returns_true_for_notifications_without_email_type(): void
    {
        $account = Account::factory()->create();
        $notification = new class extends Notification
        {
            public function via($notifiable)
            {
                return ['mail'];
            }
        };

        $result = $this->listener->handle($this->buildEvent($account, $notification));

        $this->assertTrue($result);
    }

    #[Test]
    public function it_returns_true_when_email_is_enabled(): void
    {
        $account = Account::factory()->create();
        $notification = $this->notificationFor(EmailType::ExamAccepted);

        $result = $this->listener->handle($this->buildEvent($account, $notification));

        $this->assertTrue($result);
    }

    #[Test]
    public function it_returns_false_when_email_is_disabled(): void
    {
        $account = Account::factory()->create();
        $account->setEmailEnabled(EmailType::ExamAccepted, false);

        $notification = $this->notificationFor(EmailType::ExamAccepted);

        $result = $this->listener->handle($this->buildEvent($account, $notification));

        $this->assertFalse($result);
    }

    #[Test]
    public function it_returns_true_when_notifiable_lacks_is_email_enabled_method(): void
    {
        $notifiable = new class
        {
            // No isEmailEnabled method
        };
        $notification = $this->notificationFor(EmailType::ExamAccepted);

        $result = $this->listener->handle($this->buildEvent($notifiable, $notification));

        $this->assertTrue($result);
    }

    private function notificationFor(EmailType $type): Notification
    {
        return new class($type) extends Notification implements HasEmailType
        {
            public function __construct(private EmailType $type) {}

            public function getEmailType(): EmailType
            {
                return $this->type;
            }

            public function via($notifiable)
            {
                return ['mail'];
            }
        };
    }
}
