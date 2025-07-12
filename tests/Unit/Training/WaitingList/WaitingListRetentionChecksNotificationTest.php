<?php

namespace Tests\Unit\Training\WaitingList;

use App\Notifications\Training\WaitinglistRetentionCheck;
use App\Notifications\Training\RemovedFromWaitingListFailedRetention;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Models\Training\WaitingList\WaitingListRetentionChecks;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListRetentionChecksNotificationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_sends_retention_check_notification_with_correct_data()
    {
        Notification::fake();
        $account = WaitingListAccount::create([
            'account_id' => 1,
            'list_id' => 1,
        ]);
        $check = WaitingListRetentionChecks::create([
            'waiting_list_account_id' => $account->id,
            'token' => 'TOKEN123',
            'expires_at' => now()->addDays(7),
            'status' => 'pending',
            'email_sent_at' => now(),
        ]);
        $notifiable = $account->account;
        Notification::send($notifiable, new WaitinglistRetentionCheck($check, 'TOKEN123'));
        Notification::assertSentTo($notifiable, WaitinglistRetentionCheck::class);
    }

    #[Test]
    public function it_sends_removal_notification_with_correct_data()
    {
        Notification::fake();
        $account = WaitingListAccount::create([
            'account_id' => 2,
            'list_id' => 1,
        ]);
        $check = WaitingListRetentionChecks::create([
            'waiting_list_account_id' => $account->id,
            'token' => 'TOKEN456',
            'expires_at' => now()->addDays(7),
            'status' => 'expired',
            'email_sent_at' => now()->subDays(10),
        ]);
        $notifiable = $account->account;
        Notification::send($notifiable, new RemovedFromWaitingListFailedRetention($check));
        Notification::assertSentTo($notifiable, RemovedFromWaitingListFailedRetention::class);
    }
}
