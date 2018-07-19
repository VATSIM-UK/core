<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FeedbackUserSearchTest extends TestCase
{
    use DatabaseTransactions;

    private $member;
    private $otherMember;

    public function setUp()
    {
        parent::setUp();
        
        $this->member = factory(Account::class)->create();
        $this->otherMember = factory(Account::class)->create();
    }

    /** @test * */
    public function testItDoesNotReturnCurrentUser()
    {
        $searchQuery = $this->actingAs($this->member)
                            ->get(route('mship.feedback.usersearch', $this->member->real_name))
                            ->getContent();

        $this->assertNotContains($this->member->real_name, $searchQuery);
        $this->assertNotContains($this->member->id, $searchQuery);
        /* need to assert does not contain state */
    }

    /** @test * */
    public function testItReturnsAnotherUser()
    {
        $searchQuery = $this->actingAs($this->member)
                            ->get(route('mship.feedback.usersearch', $this->otherMember->real_name))
                            ->getContent();

        $this->assertContains($this->otherMember->real_name, $searchQuery);
        $this->assertContains(strval($this->otherMember->id), $searchQuery);
        /* need to assert contains state */
    }
}
