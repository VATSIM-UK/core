<?php

namespace Tests\Unit\Support;

use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditHelperTest extends TestCase
{
    #[Test]
    public function it_writes_to_the_audit_channel_with_actor_context()
    {
        $actor = $this->user; // member-role account helper from TestCase
        $this->actingAs($actor);

        Log::shouldReceive('channel')->with('audit')->andReturnSelf();
        Log::shouldReceive('info')->once()->withArgs(function ($message, $context) use ($actor) {
            return $message === 'Test audit event'
                && $context['account_id'] === 123
                && $context['actor_id'] === $actor->id;
        });

        audit('Test audit event', ['account_id' => 123]);
    }
}
