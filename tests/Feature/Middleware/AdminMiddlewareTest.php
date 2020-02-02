<?php

namespace Tests\Feature\Middleware;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function testAGuestCannotAccessAdmEndpoints()
    {
        $this->get(route('adm.index'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function testANonStaffMemberCannotAccessAdmEndpoints()
    {
        $this->actingAs($this->user)->get('adm')
            ->assertForbidden();
    }

    /** @test */
    public function testPrivaccCanBypassGuard()
    {
        $this->actingAs($this->privacc)
            ->get('adm')
            ->assertSuccessful();
    }

    /** @test */
    public function testPrivaccDoesntWorkInProduction()
    {
        config()->set('app.env', 'production');

        $this->actingAs($this->privacc)
            ->get('adm')
            ->assertForbidden();
    }
}
