<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use App\Models\Mship\Role as RoleData;

class AdmFeedbackSendTest extends TestCase
{
    use DatabaseTransactions;

    private $account;
    private $member;

    public function setUp()
    {
        parent::setUp();

        $this->account = factory(\App\Models\Mship\Account::class)->create()->fresh();
        $this->member = factory(\App\Models\Mship\Account::class)->create()->fresh();
    }

    /** @test * */
    public function testRedirectWithoutPermission()
    {
        $response = $this->actingAs($this->account)->post(route('adm.mship.feedback.send', $this->member->id));

        $response->assertStatus(403);
    }
}
