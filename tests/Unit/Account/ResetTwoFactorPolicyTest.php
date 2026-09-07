<?php

namespace Tests\Unit\Account;

use App\Models\Mship\Account;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResetTwoFactorPolicyTest extends TestCase
{
    #[Test]
    public function it_denies_reset_without_the_permission(): void
    {
        $actor = Account::factory()->create();
        $subject = Account::factory()->create();

        $this->assertFalse($actor->can('resetTwoFactor', $subject));
    }

    #[Test]
    public function it_allows_reset_with_the_remove_password_permission(): void
    {
        $actor = Account::factory()->create();
        $actor->givePermissionTo('account.remove-password.*');
        $subject = Account::factory()->create();

        $this->assertTrue($actor->fresh()->can('resetTwoFactor', $subject));
    }

    #[Test]
    public function it_denies_self_reset_without_the_self_permission(): void
    {
        $actor = Account::factory()->create();
        $actor->givePermissionTo('account.remove-password.*');

        $this->assertFalse($actor->fresh()->can('resetTwoFactor', $actor));
    }

    #[Test]
    public function it_allows_self_reset_with_the_self_permission(): void
    {
        $actor = Account::factory()->create();
        $actor->givePermissionTo('account.remove-password.*');
        $actor->givePermissionTo('account.self');

        $this->assertTrue($actor->fresh()->can('resetTwoFactor', $actor));
    }
}
