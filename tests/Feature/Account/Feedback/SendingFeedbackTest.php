<?php

namespace Tests\Feature\Account\Feedback;

use App\Models\Mship\Feedback\Feedback;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SendingFeedbackTest extends TestCase
{
    use DatabaseTransactions;

    private $feedback;

    public function setUp(): void
    {
        parent::setUp();

        // Create some feedback feedback
        $this->feedback = factory(Feedback::class)->create([
            'account_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function testItRedirectsMemberIfThereIsNoSentFeedback()
    {
        $this->actingAs($this->user)
            ->get(route('mship.feedback.view'))
            ->assertRedirect(route('mship.manage.dashboard'));
    }

    /** @test */
    public function testItAllowsViewingIfThereIsSentFeedback()
    {
        $this->feedback->markSent($this->privacc);

        $this->actingAs($this->user)
            ->get(route('mship.feedback.view'))
            ->assertSuccessful();
    }

    /** @test */
    public function testItAllowsFeedbackToBeMarkedAsSent()
    {
        $this->actingAs($this->privacc)
            ->post(route('adm.mship.feedback.send', $this->feedback->id))
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');
    }
}
