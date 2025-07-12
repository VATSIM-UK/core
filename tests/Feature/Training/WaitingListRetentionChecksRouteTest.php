<?php

namespace Tests\Feature\Training;

use App\Models\Training\WaitingList\WaitingListAccount;
use App\Models\Training\WaitingList\WaitingListRetentionChecks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListRetentionChecksRouteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_validates_token_and_updates_status()
    {
        $account = WaitingListAccount::create([
            'account_id' => 1,
            'list_id' => 1,
        ]);
        $check = WaitingListRetentionChecks::create([
            'waiting_list_account_id' => $account->id,
            'token' => 'ROUTETOKEN',
            'expires_at' => now()->addDays(7),
            'status' => 'pending',
            'email_sent_at' => now(),
        ]);
        $url = URL::route('training.retention.token', ['token' => 'ROUTETOKEN']);
        $response = $this->get($url);
        $response->assertStatus(200);
        $check->refresh();
        $this->assertNotEquals('pending', $check->status);
    }

    #[Test]
    public function it_handles_invalid_or_expired_token_gracefully()
    {
        $url = URL::route('training.retention.token', ['token' => 'INVALIDTOKEN']);
        $response = $this->get($url);
        $response->assertStatus(404);
    }
}
