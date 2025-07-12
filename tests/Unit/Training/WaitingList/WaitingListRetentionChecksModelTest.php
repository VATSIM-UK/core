<?php

namespace Tests\Unit\Training\WaitingList;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Training\WaitingList\WaitingListRetentionChecks;
use App\Models\Training\WaitingList\WaitingListAccount;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListRetentionChecksModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_and_retrieve_retention_check()
    {
        $account = WaitingListAccount::create([
            'account_id' => 1,
            'list_id' => 1,
        ]);
        $check = WaitingListRetentionChecks::create([
            'waiting_list_account_id' => $account->id,
            'token' => 'SAMPLETOKEN',
            'expires_at' => now()->addDays(7),
            'status' => 'pending',
            'email_sent_at' => now(),
        ]);
        $this->assertEquals('pending', $check->status);
        $this->assertEquals($account->id, $check->waiting_list_account_id);
        $this->assertDatabaseHas('training_waiting_list_retention_checks', [
            'token' => 'SAMPLETOKEN',
        ]);
    }

    #[Test]
    public function it_relates_to_waiting_list_account()
    {
        $account = WaitingListAccount::create([
            'account_id' => 2,
            'list_id' => 1,
        ]);
        $check = WaitingListRetentionChecks::create([
            'waiting_list_account_id' => $account->id,
            'token' => 'RELATIONTOKEN',
            'expires_at' => now()->addDays(7),
            'status' => 'pending',
            'email_sent_at' => now(),
        ]);
        $this->assertEquals($account->id, $check->waiting_list_account->id);
    }
}
