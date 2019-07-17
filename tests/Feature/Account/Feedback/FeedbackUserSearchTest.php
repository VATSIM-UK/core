<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FeedbackUserSearchTest extends TestCase
{
    use DatabaseTransactions;

    private $otherUser;

    public function setUp():void
    {
        parent::setUp();

        $this->otherUser = factory(Account::class)->create();
    }

    /** @test */
    public function testItReturnsAnotherUser()
    {
        $searchQuery = $this->actingAs($this->user)
            ->get(route('mship.feedback.usersearch', $this->otherUser->real_name))
            ->getContent();

        $this->assertContains(e($this->otherUser->real_name), $searchQuery);
        $this->assertContains((string)($this->otherUser->id), $searchQuery);
    }

    /** @test */
    public function testItDoesNotReturnCurrentUser()
    {
        $searchQuery = $this->actingAs($this->user)
            ->get(route('mship.feedback.usersearch', $this->user->real_name))
            ->getContent();

        $this->assertNotContains(e($this->user->real_name), $searchQuery);
        $this->assertNotContains((string)($this->user->id), $searchQuery);
    }
}
