<?php

namespace Tests\Feature;

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

        $this->otherUser = factory(Account::class)->create();
    }

    /** @test */
    public function testItReturnsAnotherUser()
    {
        $searchQuery = $this->actingAs($this->user)
            ->get(route('mship.feedback.usersearch', $this->otherUser->real_name))
            ->getContent();

        $this->assertStringContainsString($this->otherUser->real_name, $searchQuery);
        $this->assertStringContainsString((string) ($this->otherUser->id), $searchQuery);
    }

    /** @test */
    public function testItDoesNotReturnCurrentUser()
    {
        $searchQuery = $this->actingAs($this->user)
            ->get(route('mship.feedback.usersearch', $this->user->real_name))
            ->getContent();

        $this->assertStringNotContainsString($this->user->real_name, $searchQuery);
        $this->assertStringNotContainsString((string) ($this->user->id), $searchQuery);
    }
}
