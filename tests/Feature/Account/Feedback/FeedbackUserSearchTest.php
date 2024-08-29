<?php

namespace Tests\Feature\Account\Feedback;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FeedbackUserSearchTest extends TestCase
{
    use DatabaseTransactions;

    private $otherUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->otherUser = Account::factory()->create();
    }

    /** @test */
    public function testItReturnsAnotherUserByName()
    {
        $searchQuery = $this->actingAs($this->user)
            ->get(route('mship.feedback.usersearch', $this->otherUser->real_name))
            ->getContent();

        $this->assertStringContainsString(e($this->otherUser->real_name), $searchQuery);
        $this->assertStringContainsString((string) $this->otherUser->id, $searchQuery);
    }

    /** @test */
    public function testItReturnsAnotherUserById()
    {
        $searchQuery = $this->actingAs($this->user)
            ->get(route('mship.feedback.usersearch', $this->otherUser->id))
            ->getContent();

        $this->assertStringContainsString(e($this->otherUser->real_name), $searchQuery);
        $this->assertStringContainsString((string) $this->otherUser->id, $searchQuery);
    }

    /** @test */
    public function testItDoesNotReturnCurrentUser()
    {
        $searchQuery = $this->actingAs($this->user)
            ->get(route('mship.feedback.usersearch', $this->user->real_name))
            ->getContent();

        $this->assertStringNotContainsString(e($this->user->real_name), $searchQuery);
        $this->assertStringNotContainsString((string) $this->user->id, $searchQuery);
    }
}
