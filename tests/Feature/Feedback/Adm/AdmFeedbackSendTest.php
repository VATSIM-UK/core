<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

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
