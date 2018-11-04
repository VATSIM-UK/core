<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Mship\Account;

class AdminMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    /** @test * */
    public function testANonStaffMemberCannotAccessAdmEndpoints()
    {
        $user = factory(Account::class)->create();

        $this->actingAs($user, 'web')->get(route('adm.mship.feedback.new'))->assertForbidden();
    }
}
