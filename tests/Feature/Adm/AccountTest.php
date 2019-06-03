<?php

namespace Tests\Feature\Adm;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;

    public function setUp(): void
    {
        parent::setUp();

        $admin = factory(Account::class)->create();
        $admin->assignRole('privacc');
        $this->admin = $admin->fresh();
    }

    /** @test **/
    public function testGetA404WhenTryingToViewNonExistentUser()
    {
        $this->actingAs($this->admin)
                ->get(route('adm.mship.account.details', '12345'))
                ->assertNotFound();
    }
}
