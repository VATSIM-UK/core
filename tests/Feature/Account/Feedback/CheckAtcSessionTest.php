<?php

namespace Tests\Feature\Account\Feedback;

use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckAtcSessionTest extends TestCase
{
    #[Test]
    public function test_it_reports_an_invalid_cid_separately_from_the_datetime()
    {
        $response = $this->actingAs($this->user, 'web')
            ->postJson(route('mship.feedback.check-atc-session'), [
                'cid' => 99999999,
                'datetime' => now()->subMinutes(10)->format('Y-m-d H:i'),
            ]);

        $response->assertStatus(422)
            ->assertJson(['valid' => false])
            ->assertJsonPath('message', 'Please enter a valid CID for a home UK member.');
    }

    #[Test]
    public function test_it_reports_an_invalid_datetime_separately_from_the_cid()
    {
        $response = $this->actingAs($this->user, 'web')
            ->postJson(route('mship.feedback.check-atc-session'), [
                'cid' => Account::factory()->createQuietly()->id,
                'datetime' => 'not-a-date',
            ]);

        $response->assertStatus(422)
            ->assertJson(['valid' => false])
            ->assertJsonPath('message', 'Please enter a valid date and time.');
    }

    #[Test]
    public function test_it_reports_both_cid_and_datetime_errors_when_both_are_invalid()
    {
        $response = $this->actingAs($this->user, 'web')
            ->postJson(route('mship.feedback.check-atc-session'), [
                'cid' => 'not-a-cid',
                'datetime' => 'not-a-date',
            ]);

        $response->assertStatus(422)
            ->assertJson(['valid' => false])
            ->assertJsonPath('message', 'Please enter a valid CID for a home UK member. Please enter a valid date and time.');
    }

    #[Test]
    public function test_it_reports_when_no_session_was_found_for_a_valid_cid()
    {
        $response = $this->actingAs($this->user, 'web')
            ->postJson(route('mship.feedback.check-atc-session'), [
                'cid' => Account::factory()->createQuietly()->id,
                'datetime' => now()->subMinutes(10)->format('Y-m-d H:i'),
            ]);

        $response->assertSuccessful()
            ->assertJson(['valid' => false]);

        $this->assertStringContainsString('could not find a controlling session', $response->json('message'));
    }

    #[Test]
    public function test_it_passes_when_a_session_exists_around_the_given_time()
    {
        $account = Account::factory()->createQuietly();
        $eventTime = now()->subMinutes(10);

        $session = new Atc([
            'account_id' => $account->id,
            'qualification_id' => 1,
            'callsign' => 'EGLL_TWR',
            'frequency' => 118.500,
            'facility_type' => Atc::TYPE_TWR,
            'connected_at' => $eventTime,
            'disconnected_at' => $eventTime->copy()->addMinutes(10),
        ]);
        $session->timestamps = false;
        $session->save();

        $response = $this->actingAs($this->user, 'web')
            ->postJson(route('mship.feedback.check-atc-session'), [
                'cid' => $account->id,
                'datetime' => $eventTime->format('Y-m-d H:i'),
            ]);

        $response->assertSuccessful()
            ->assertJson(['valid' => true, 'message' => null]);
    }

    #[Test]
    public function test_it_rejects_leaving_feedback_about_yourself()
    {
        $response = $this->actingAs($this->user, 'web')
            ->postJson(route('mship.feedback.check-atc-session'), [
                'cid' => $this->user->id,
                'datetime' => now()->subMinutes(10)->format('Y-m-d H:i'),
            ]);

        $response->assertSuccessful()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('message', 'You cannot leave feedback about yourself.');
    }
}
