<?php

namespace Tests\Feature\Account;

use App\Models\TeamSpeak\Registration;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TeamspeakManagementTest extends TestCase
{
    use DatabaseTransactions;

    private $registration;

    #[Test]
    public function test_user_can_delete_own_registration()
    {
        $this->from(route('mship.manage.dashboard'))->followingRedirects()->actingAs($this->registration->account)
            ->post(route('teamspeak.delete', ['mshipRegistration' => $this->registration->id]))
            ->assertSuccessful();
    }

    #[Test]
    public function test_user_cant_delete_others_registration()
    {
        $this->followingRedirects()->actingAs($this->user)
            ->post(route('teamspeak.delete', $this->registration))
            ->assertNotFound();
    }

    #[Test]
    public function test_can_get_status_of_own_registration()
    {
        $this->followingRedirects()->actingAs($this->registration->account)
            ->post(route('teamspeak.status', $this->registration))
            ->assertSuccessful();
    }

    #[Test]
    public function test_cant_get_status_of_others_registration()
    {
        $this->followingRedirects()->actingAs($this->user)
            ->post(route('teamspeak.status', $this->registration))
            ->assertNotFound();
    }

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.teamspeak.host' => null,
            'services.teamspeak.username' => null,
            'services.teamspeak.password' => null,
            'services.teamspeak.port' => null,
            'services.teamspeak.query_port' => null,
        ]);

        $this->registration = factory(Registration::class)->create();
    }
}
