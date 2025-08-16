<?php

namespace Tests\Unit\Training\WaitingList\WaitingListRetentionChecks;

use App\Jobs\Training\ActionWaitingListRetentionCheckRemoval;
use App\Jobs\Training\SendWaitingListRetentionCheck;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Unit\Training\WaitingList\WaitingListTestHelper;

class WaitingListRetentionChecksNotificationsTest extends TestCase
{
    use DatabaseTransactions, WaitingListTestHelper;

    #[Test]
    public function it_sends_retention_email_and_records_email_sent_at()
    {
        $waitingList = $this->createList();
        $account = Account::factory()->create([
            'id' => 1,
        ]);

        $waitingListAccount = $waitingList->addToWaitingList($account, $this->privacc);

        $job = new SendWaitingListRetentionCheck($waitingListAccount);
        $job->handle();

        $generatedRetentionCheck = WaitingListRetentionCheck::where('waiting_list_account_id', $waitingListAccount->id)
            ->where('status', WaitingListRetentionCheck::STATUS_PENDING)
            ->first();

        $this->assertEquals(WaitingListRetentionCheck::STATUS_PENDING, $generatedRetentionCheck->status);
        $this->assertNotNull($generatedRetentionCheck->token);
        $this->assertTrue($generatedRetentionCheck->expires_at->isFuture());
        $this->assertNotNull($generatedRetentionCheck->email_sent_at);
    }

    #[Test]
    public function it_does_not_remove_account_when_notification_fails()
    {
        $waitingList = $this->createList();
        $account = Account::factory()->create();

        $waitingListAccount = $waitingList->addToWaitingList($account, $this->privacc);

        $retentionCheck = WaitingListRetentionCheck::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'status' => WaitingListRetentionCheck::STATUS_PENDING,
            'token' => 'test_token',
            'expires_at' => now()->addDays(7),
            'email_sent_at' => now(),
        ]);

        // Store initial state for comparison
        $initialWaitingListAccountCount = $waitingList->waitingListAccounts()->count();

        // Mock the account to throw an exception when notify() is called
        $accountMock = \Mockery::mock($account)->makePartial();
        $accountMock->shouldReceive('notify')
            ->once()
            ->andThrow(new \Exception('Notification failed'));

        // Create a partial mock of the waiting list account to return our mocked account
        $waitingListAccountMock = \Mockery::mock($waitingListAccount)->makePartial();
        $waitingListAccountMock->shouldReceive('getAttribute')
            ->with('account')
            ->andReturn($accountMock);

        // Create a partial mock of the retention check to return our mocked waiting list account
        $retentionCheckMock = \Mockery::mock($retentionCheck)->makePartial();
        $retentionCheckMock->shouldReceive('getAttribute')
            ->with('waitingListAccount')
            ->andReturn($waitingListAccountMock);

        /** @var WaitingListRetentionCheck $retentionCheckMock */
        $job = new ActionWaitingListRetentionCheckRemoval($retentionCheckMock);

        // Execute the job - it should handle the exception gracefully
        $job->handle();

        // Refresh models to get latest state from database
        $retentionCheck->refresh();
        $waitingListAccount->refresh();
        $waitingList->refresh();

        // Assert the account was NOT removed from the waiting list
        $this->assertEquals($initialWaitingListAccountCount, $waitingList->waitingListAccounts()->count());
        $this->assertTrue($waitingListAccount->exists);
        $this->assertNotNull($waitingListAccount->waitingList);

        // Assert the retention check was NOT marked as expired/completed
        $this->assertEquals(WaitingListRetentionCheck::STATUS_PENDING, $retentionCheck->status);
        $this->assertNull($retentionCheck->removal_actioned_at);
    }

    #[Test]
    public function it_sends_removal_email()
    {
        $waitingList = $this->createPopulatedList();
        $waitingListAccount = $waitingList->waitingListAccounts()->first();

        $retentionCheck = WaitingListRetentionCheck::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'status' => WaitingListRetentionCheck::STATUS_PENDING,
            'token' => 'test_token',
            'expires_at' => now()->addDays(7),
            'email_sent_at' => now(),
        ]);

        $job = new ActionWaitingListRetentionCheckRemoval($retentionCheck);
        $job->handle();

        $retentionCheck->refresh();
        $this->assertEquals(WaitingListRetentionCheck::STATUS_EXPIRED, $retentionCheck->status);
        $this->assertTrue($retentionCheck->removal_actioned_at->isPast() || $retentionCheck->removal_actioned_at->equalTo(now()));

        $this->assertNull($retentionCheck->waitingListAccount);
    }

    #[Test]
    public function it_does_not_create_retention_check_when_notification_fails()
    {
        $waitingList = $this->createList();
        $account = Account::factory()->create();

        $waitingListAccount = $waitingList->addToWaitingList($account, $this->privacc);

        // Mock the account to throw an exception when notify() is called
        $accountMock = \Mockery::mock($account)->makePartial();
        $accountMock->shouldReceive('notify')
            ->once()
            ->andThrow(new \Exception('Notification failed'));

        // Create a partial mock of the waiting list account to return our mocked account
        $waitingListAccountMock = \Mockery::mock($waitingListAccount)->makePartial();
        $waitingListAccountMock->shouldReceive('getAttribute')
            ->with('account')
            ->andReturn($accountMock);

        /** @var \App\Models\Training\WaitingList\WaitingListAccount $waitingListAccountMock */
        $job = new SendWaitingListRetentionCheck($waitingListAccountMock);

        // Execute the job - it should handle the exception gracefully
        $job->handle();

        // Assert that NO retention check record was created (transaction was rolled back)
        $retentionChecks = WaitingListRetentionCheck::where('waiting_list_account_id', $waitingListAccount->id)->get();
        $this->assertCount(0, $retentionChecks, 'No retention check should be created when notification fails');
    }

    #[Test]
    public function it_logs_error_when_send_notification_fails()
    {
        $waitingList = $this->createList();
        $account = Account::factory()->create([
            'id' => 1,
        ]);

        $waitingListAccount = $waitingList->addToWaitingList($account, $this->privacc);

        // Spy on Log after the waiting list account is created to avoid interference with event logging
        Log::spy();

        // Mock the account to throw an exception when notify() is called
        $accountMock = \Mockery::mock($account)->makePartial();
        $accountMock->shouldReceive('notify')
            ->once()
            ->andThrow(new \Exception('Notification failed'));

        // Create a partial mock of the waiting list account to return our mocked account
        $waitingListAccountMock = \Mockery::mock($waitingListAccount)->makePartial();
        $waitingListAccountMock->shouldReceive('getAttribute')
            ->with('account')
            ->andReturn($accountMock);

        /** @var \App\Models\Training\WaitingList\WaitingListAccount $waitingListAccountMock */
        $job = new SendWaitingListRetentionCheck($waitingListAccountMock);

        // Execute the job - it should handle the exception gracefully
        $job->handle();

        // Assert that the error was logged correctly
        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(function ($message) use ($account) {
                return str_contains($message, "Failed to notify account {$account->id}") &&
                       str_contains($message, 'of retention check') &&
                       str_contains($message, 'Notification failed');
            });
    }

    #[Test]
    public function it_commits_transaction_when_notification_succeeds()
    {
        $waitingList = $this->createList();
        $account = Account::factory()->create([
            'id' => 1,
        ]);

        $waitingListAccount = $waitingList->addToWaitingList($account, $this->privacc);

        $job = new SendWaitingListRetentionCheck($waitingListAccount);
        $job->handle();

        // Assert that the retention check record was created and persisted (transaction was committed)
        $retentionCheck = WaitingListRetentionCheck::where('waiting_list_account_id', $waitingListAccount->id)
            ->where('status', WaitingListRetentionCheck::STATUS_PENDING)
            ->first();

        $this->assertNotNull($retentionCheck, 'Retention check should be created when notification succeeds');
        $this->assertEquals(WaitingListRetentionCheck::STATUS_PENDING, $retentionCheck->status);
        $this->assertNotNull($retentionCheck->token);
        $this->assertTrue($retentionCheck->expires_at->isFuture());
        $this->assertNotNull($retentionCheck->email_sent_at);
    }
}
