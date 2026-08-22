<?php

namespace Tests\Unit\Account;

use App\Models\Mship\Account;
use App\Notifications\Mship\TwoFactorReset;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TwoFactorResetNotificationTest extends TestCase
{
    #[Test]
    public function it_names_the_administrator_and_omits_any_reason(): void
    {
        $member = Account::factory()->create();
        $administrator = Account::factory()->create();

        $mail = (new TwoFactorReset($administrator))->toMail($member);
        $rendered = $mail->render();

        $this->assertStringContainsString($administrator->name, $rendered);
        $this->assertStringContainsString('two-factor authentication', strtolower($rendered));
        $this->assertStringNotContainsString('HELPDESK-1234', $rendered);
    }

    #[Test]
    public function it_sends_by_mail_and_database(): void
    {
        $member = Account::factory()->create();
        $administrator = Account::factory()->create();

        $this->assertEquals(['mail', 'database'], (new TwoFactorReset($administrator))->via($member));
    }
}
