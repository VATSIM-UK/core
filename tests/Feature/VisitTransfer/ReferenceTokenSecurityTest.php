<?php

namespace Tests\Feature\VisitTransfer;

use App\Models\Mship\Account;
use App\Models\VisitTransfer\Reference;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReferenceTokenSecurityTest extends TestCase
{
    #[Test]
    public function cancel_endpoint_requires_reference_owner()
    {
        $owner = Account::factory()->createQuietly();
        $otherUser = Account::factory()->createQuietly();

        $reference = Reference::factory()->create([
            'account_id' => $owner->id,
            'status' => Reference::STATUS_REQUESTED,
        ]);
        $token = $reference->generateToken();

        $this->actingAs($otherUser)
            ->post(route('visiting.reference.complete.cancel', [$token->code]))
            ->assertForbidden();

        $this->assertEquals(Reference::STATUS_REQUESTED, $reference->fresh()->status);
    }

    #[Test]
    public function complete_page_rejects_expired_tokens()
    {
        $owner = Account::factory()->createQuietly();
        $reference = Reference::factory()->create([
            'account_id' => $owner->id,
            'status' => Reference::STATUS_REQUESTED,
        ]);
        $token = $reference->generateToken();
        $token->expires_at = now()->subMinute();
        $token->save();

        $this->actingAs($owner)
            ->get(route('visiting.reference.complete', [$token->code]))
            ->assertForbidden();
    }

    #[Test]
    public function complete_submission_rejects_used_tokens()
    {
        $owner = Account::factory()->createQuietly();
        $reference = Reference::factory()->create([
            'account_id' => $owner->id,
            'status' => Reference::STATUS_REQUESTED,
        ]);
        $token = $reference->generateToken();
        $token->consume();

        $this->actingAs($owner)
            ->post(route('visiting.reference.complete.post', [$token->code]), $this->validCompletionPayload())
            ->assertForbidden();

        $this->assertEquals(Reference::STATUS_REQUESTED, $reference->fresh()->status);
        $this->assertNull($reference->fresh()->submitted_at);
    }

    #[Test]
    public function complete_submission_rejects_non_reference_token_type()
    {
        $owner = Account::factory()->createQuietly();
        $reference = Reference::factory()->create([
            'account_id' => $owner->id,
            'status' => Reference::STATUS_REQUESTED,
        ]);
        $token = $reference->generateToken();
        $token->type = 'mship_account_email_verify';
        $token->save();

        $this->actingAs($owner)
            ->post(route('visiting.reference.complete.post', [$token->code]), $this->validCompletionPayload())
            ->assertForbidden();

        $this->assertEquals(Reference::STATUS_REQUESTED, $reference->fresh()->status);
        $this->assertNull($reference->fresh()->submitted_at);
    }

    #[Test]
    public function complete_submission_succeeds_with_valid_token()
    {
        $owner = Account::factory()->createQuietly();
        $reference = Reference::factory()->create([
            'account_id' => $owner->id,
            'status' => Reference::STATUS_REQUESTED,
        ]);
        $token = $reference->generateToken();

        $this->actingAs($owner)
            ->post(route('visiting.reference.complete.post', [$token->code]), $this->validCompletionPayload())
            ->assertRedirect(route('visiting.landing'));

        $this->assertEquals(Reference::STATUS_UNDER_REVIEW, $reference->fresh()->status);
        $this->assertNotNull($reference->fresh()->submitted_at);
        $this->assertNotNull($token->fresh()->used_at);
    }

    private function validCompletionPayload(): array
    {
        return [
            'reference_relationship' => 'yes',
            'reference_hours_minimum' => 'yes',
            'reference_recent_transfer' => 'yes',
            'reference' => str_repeat('A', 60),
        ];
    }
}
