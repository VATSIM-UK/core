<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function testAGuestCannotAccessAdmEndpoints()
    {
        $this->get(route('adm.mship.feedback.new'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function testANonStaffMemberCannotAccessAdmEndpoints()
    {
        $this->actingAs($this->user)->get('adm/dashboard')
            ->assertForbidden();
    }

    /** @test */
    public function testPrivaccCanBypassGuard()
    {
        $this->actingAs($this->privacc)
            ->get('adm/dashboard')
            ->assertSuccessful();
    }

    /** @test */
    public function testPrivaccDoesntWorkInProduction()
    {
        config()->set('app.env', 'production');

        $this->actingAs($this->privacc)
            ->get('adm/dashboard')
            ->assertForbidden();
    }
}
