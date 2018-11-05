<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    /** @test * */
    public function testAGuestCannotAccessAdmEndpoints()
    {
        $this->get(route('adm.mship.feedback.new'))
                ->assertRedirect(route('login'));
    }

    /** @test * */
    public function testANonStaffMemberCannotAccessAdmEndpoints()
    {
        $user = factory(Account::class)->create();

        $this->actingAs($user)->get('adm/')
                ->assertForbidden();
    }

    /** @test */
    public function testPrivaccCanBypassGuard()
    {
        $this->actingAs($this->privacc)
                ->get('adm/')
                ->assertRedirect(route('adm.dashboard'));
    }
}
