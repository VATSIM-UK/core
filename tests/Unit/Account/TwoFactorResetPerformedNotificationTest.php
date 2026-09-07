<?php

namespace Tests\Unit\Account;

use App\Models\Contact;
use App\Models\Mship\Account;
use App\Notifications\Mship\TwoFactorResetPerformed;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TwoFactorResetPerformedNotificationTest extends TestCase
{
    #[Test]
    public function it_names_the_target_administrator_and_reason(): void
    {
        $target = Account::factory()->create();
        $administrator = Account::factory()->create();
        $recipient = factory(Contact::class)->create(['name' => 'Privileged Access']);

        $mail = (new TwoFactorResetPerformed($target, $administrator, 'HELPDESK-1234: lost device'))->toMail($recipient);
        $rendered = $mail->render();

        $this->assertStringContainsString($administrator->name, $rendered);
        $this->assertStringContainsString($target->name, $rendered);
        $this->assertStringContainsString('HELPDESK-1234: lost device', $rendered);
    }

    #[Test]
    public function it_sends_by_mail_and_database(): void
    {
        $target = Account::factory()->create();
        $administrator = Account::factory()->create();
        $recipient = factory(Contact::class)->create(['name' => 'Privileged Access']);

        $this->assertEquals(
            ['mail', 'database'],
            (new TwoFactorResetPerformed($target, $administrator, 'HELPDESK-1234'))->via($recipient)
        );
    }
}
