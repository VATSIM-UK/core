<?php

namespace Tests\Feature\Account\Feedback;

use App\Models\Mship\Feedback\Feedback;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendingFeedbackTest extends TestCase
{
    use DatabaseTransactions;

    private $feedback;

    protected function setUp(): void
    {
        parent::setUp();

        // Create some feedback feedback
        $this->feedback = factory(Feedback::class)->create([
            'account_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function test_it_redirects_member_if_there_is_no_sent_feedback()
    {
        $this->actingAs($this->user)
            ->get(route('mship.feedback.view'))
            ->assertRedirect(route('mship.manage.dashboard'));
    }

    #[Test]
    public function test_it_allows_viewing_if_there_is_sent_feedback()
    {
        $this->feedback->markSent($this->privacc);

        $this->actingAs($this->user)
            ->get(route('mship.feedback.view'))
            ->assertSuccessful();
    }
}
