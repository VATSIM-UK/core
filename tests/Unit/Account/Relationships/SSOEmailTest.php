<?php

namespace Tests\Unit\Account\Relationships;

use App\Models\Mship\Account\Email;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Client;
use Tests\TestCase;

class SSOEmailTest extends TestCase
{
    use DatabaseTransactions;

    private $email;

    private $ssoService;

    private $secondEmail;

    protected function setUp(): void
    {
        parent::setUp();

        $this->email = factory(Email::class)->create();
        $this->ssoService = factory(Client::class)->create();
        $this->secondEmail = factory(Email::class)->create(['account_id' => $this->email->account->id]);
    }

    /** @test */
    public function it_can_assign_sso_email()
    {
        $this->assertEquals(0, \App\Models\Sso\Email::count());

        $this->email->assignToSso($this->ssoService);
        $this->assertDatabaseHas('mship_oauth_emails', ['account_email_id' => $this->email->id, 'sso_account_id' => $this->ssoService->id]);
    }

    /** @test */
    public function it_can_change_assignment()
    {
        $this->email->assignToSso($this->ssoService);
        $this->assertEquals(1, \App\Models\Sso\Email::count());

        $this->secondEmail->assignToSso($this->ssoService);
        $this->assertDatabaseMissing('mship_oauth_emails', ['account_email_id' => $this->email->id, 'sso_account_id' => $this->ssoService->id]);
        $this->assertDatabaseHas('mship_oauth_emails', ['account_email_id' => $this->secondEmail->id, 'sso_account_id' => $this->ssoService->id]);
        $this->assertEquals(1, \App\Models\Sso\Email::count());
    }
}
