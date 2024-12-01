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
    public function test_user_can_delete_own_registration()
    {
        $this->followingRedirects()->actingAs($this->registration->account)
            ->get(route('teamspeak.delete', $this->registration))
            ->assertSuccessful();
    }

    /** @test */
    public function test_user_cant_delete_others_registration()
    {
        $this->followingRedirects()->actingAs($this->user)
            ->get(route('teamspeak.delete', $this->registration))
            ->assertNotFound();
    }

    /** @test */
    public function test_can_get_status_of_own_registration()
    {
        $this->followingRedirects()->actingAs($this->registration->account)
            ->post(route('teamspeak.status', $this->registration))
            ->assertSuccessful();
    }

    /** @test */
    public function test_cant_get_status_of_others_registration()
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
