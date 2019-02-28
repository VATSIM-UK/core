<?php
namespace Tests\Feature\Mship;

use App\Models\Mship\Account;
use App\Models\TeamSpeak\Registration;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TeamspeakManagementTest extends TestCase
{
    use DatabaseTransactions;

    private $registration;

    protected function setUp()
    {
        parent::setUp();

        $this->registration = factory(Registration::class)->create();
    }

    /** @test **/
    public function testUserCanDeleteOwnRegistration()
    {
        $this->followingRedirects()->actingAs($this->registration->account)
            ->get(route('teamspeak.delete', $this->registration))
            ->assertSuccessful();
    }

    /** @test **/
    public function testUserCantDeleteOthersRegistration()
    {
        $account = factory(Account::class)->create();
        $this->followingRedirects()->actingAs($account)
            ->get(route('teamspeak.delete', $this->registration))
            ->assertNotFound();
    }

    /** @test **/
    public function testCanGetStatusOfOwnRegistration()
    {
        $this->followingRedirects()->actingAs($this->registration->account)
            ->post(route('teamspeak.status', $this->registration))
            ->assertSuccessful();
    }

    /** @test **/
    public function testCantGetStatusOfOthersRegistration()
    {
        $account = factory(Account::class)->create();
        $this->followingRedirects()->actingAs($account)
            ->post(route('teamspeak.status', $this->registration))
            ->assertNotFound();
    }
}
