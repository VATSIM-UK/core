<?php

namespace Tests\Feature\Account;

use App\Models\Mship\Account;
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
            ->get(route('teamspeak.delete', ['mshipRegistration' => $this->registration->id]))
            ->assertSuccessful();
    }

    #[Test]
    public function test_user_cant_delete_others_registration()
    {
        $this->followingRedirects()->actingAs($this->user)
            ->get(route('teamspeak.delete', $this->registration))
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

    #[Test]
    public function test_user_with_more_than_25_registrations_is_redirected_from_new_registration()
    {
        $account = factory(Account::class)->create();
        factory(Registration::class, 26)->create(['account_id' => $account->id]);

        $this->actingAs($account)
            ->get(route('teamspeak.new'))
            ->assertRedirect(route('mship.manage.dashboard'));
    }

    #[Test]
    public function test_user_with_25_registrations_can_open_new_registration_page()
    {
        $account = factory(Account::class)->create();
        factory(Registration::class, 25)->create(['account_id' => $account->id]);

        $this->actingAs($account)
            ->get(route('teamspeak.new'))
            ->assertSuccessful();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->registration = factory(Registration::class)->create();
    }
}
