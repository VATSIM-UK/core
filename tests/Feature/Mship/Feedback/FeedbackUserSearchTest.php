<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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
    public function testItReturnsAnotherUser()
    {
        $searchQuery = $this->actingAs($this->member)
            ->get(route('mship.feedback.usersearch', $this->otherMember->real_name))
            ->getContent();

        $this->assertContains(e($this->otherMember->real_name), $searchQuery);
        $this->assertContains((string) ($this->otherMember->id), $searchQuery);
        /* need to assert contains state */
    }

    /** @test * */
    public function testItDoesNotReturnCurrentUser()
    {
        $searchQuery = $this->actingAs($this->member)
                            ->get(route('mship.feedback.usersearch', $this->member->real_name))
                            ->getContent();

        $this->assertNotContains(e($this->member->real_name), $searchQuery);
        $this->assertNotContains((string) ($this->member->id), $searchQuery);
        /* need to assert does not contain state */
    }
}
