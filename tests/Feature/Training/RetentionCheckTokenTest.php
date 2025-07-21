<?php

namespace Tests\Feature\Training;

use App\Models\Training\WaitingList\WaitingListRetentionChecks;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RetentionCheckTokenTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_redirects_to_fail_with_no_token()
    {
        $this->actingAs($this->user)
            ->get(route('mship.waiting-lists.retention.token'))
            ->assertStatus(302)
            ->assertRedirect(route('mship.waiting-lists.retention.fail'));
    }

    #[Test]
    public function it_redirects_to_fail_with_invalid_token()
    {
        $this->actingAs($this->user)
            ->get(route('mship.waiting-lists.retention.token', ['token' => 'invalid']))
            ->assertStatus(302)
            ->assertRedirect(route('mship.waiting-lists.retention.fail'));
    }

    #[Test]
    public function it_redirects_to_fail_with_processed_expired_token()
    {
        WaitingListRetentionChecks::factory()->create([
            'token' => 'expired-token',
            'expires_at' => now()->subDays(1),
            'status' => WaitingListRetentionChecks::STATUS_EXPIRED,
        ]);

        $this->actingAs($this->user)
            ->get(route('mship.waiting-lists.retention.token', ['token' => 'expired-token']))
            ->assertStatus(302)
            ->assertRedirect(route('mship.waiting-lists.retention.fail'));
    }

    #[Test]
    public function it_redirects_to_fail_with_unprocessed_expired_token()
    {
        WaitingListRetentionChecks::factory()->create([
            'token' => 'expired-token',
            'expires_at' => now()->subDays(1),
            'status' => WaitingListRetentionChecks::STATUS_PENDING,
        ]);

        $this->actingAs($this->user)
            ->get(route('mship.waiting-lists.retention.token', ['token' => 'expired-token']))
            ->assertStatus(302)
            ->assertRedirect(route('mship.waiting-lists.retention.fail'));
    }

    #[Test]
    public function it_processes_and_redirects_valid_token_request()
    {
        WaitingListRetentionChecks::factory()->create([
            'token' => 'valid-token',
            'expires_at' => now()->addDays(7),
            'status' => WaitingListRetentionChecks::STATUS_PENDING,
        ]);

        $this->actingAs($this->user)
            ->get(route('mship.waiting-lists.retention.token', ['token' => 'valid-token']))
            ->assertStatus(302)
            ->assertRedirect(route('mship.waiting-lists.retention.success'));

        $this->assertDatabaseHas((new WaitingListRetentionChecks)->getTable(), [
            'token' => 'valid-token',
            'status' => WaitingListRetentionChecks::STATUS_USED,
        ]);
    }
}
