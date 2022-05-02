<?php

namespace Tests\Feature\Account;

use App\Models\TeamSpeak\Registration;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TeamspeakManagementTest extends TestCase
{
    use DatabaseTransactions;

    private $registration;

    /** @test */
    public function testUserCanDeleteOwnRegistration()
    {
        $this->followingRedirects()->actingAs($this->registration->account)
            ->get(route('teamspeak.delete', $this->registration))
            ->assertSuccessful();
    }

    /** @test */
    public function testUserCantDeleteOthersRegistration()
    {
        $this->followingRedirects()->actingAs($this->user)
            ->get(route('teamspeak.delete', $this->registration))
            ->assertNotFound();
    }

    /** @test */
    public function testCanGetStatusOfOwnRegistration()
    {
        $this->followingRedirects()->actingAs($this->registration->account)
            ->post(route('teamspeak.status', $this->registration))
            ->assertSuccessful();
    }

    /** @test */
    public function testCantGetStatusOfOthersRegistration()
    {
        $this->followingRedirects()->actingAs($this->user)
            ->post(route('teamspeak.status', $this->registration))
            ->assertNotFound();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->registration = factory(Registration::class)->create();
    }
}
