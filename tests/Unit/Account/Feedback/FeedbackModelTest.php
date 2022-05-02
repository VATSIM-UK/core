<?php

namespace Tests\Unit\Account\Feedback;

use App\Models\Mship\Feedback\Feedback;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FeedbackModelTest extends TestCase
{
    use DatabaseTransactions;

    private $feedback;

    public function setUp(): void
    {
        parent::setUp();
        $this->feedback = factory(Feedback::class)->create();
    }

    /** @test */
    public function itMarksTheFeedbackAsActioned()
    {
        $this->assertNull($this->feedback->actioned_at);
        $this->assertNull($this->feedback->actioned_comment);
        $this->assertNull($this->feedback->actioned_by_id);

        $this->feedback->markActioned($this->privacc, 'Test');

        $this->assertNotNull($this->feedback->actioned_at);
        $this->assertNotNull($this->feedback->actioned_comment);
        $this->assertEquals($this->privacc->id, $this->feedback->actioned_by_id);
    }

    /** @test */
    public function itMarksTheFeedbackAsUnActioned()
    {
        $this->feedback->markActioned($this->privacc);

        $this->feedback->markUnActioned();

        $this->assertNull($this->feedback->actioned_at);
        $this->assertNull($this->feedback->actioned_comment);
        $this->assertNull($this->feedback->actioned_by_id);
    }

    /** @test */
    public function itMarksTheFeedbackAsSent()
    {
        $comment = 'Test';

        $this->assertNull($this->feedback->sent_at);
        $this->assertNull($this->feedback->sent_comment);
        $this->assertNull($this->feedback->sent_by_id);
        $this->assertNull($this->feedback->actioned_at);
        $this->assertNull($this->feedback->actioned_comment);
        $this->assertNull($this->feedback->actioned_by_id);

        $this->feedback->markSent($this->privacc, $comment);

        $this->assertNotNull($this->feedback->sent_at);
        $this->assertEquals($comment, $this->feedback->sent_comment);
        $this->assertEquals($this->privacc->id, $this->feedback->sent_by_id);

        /* Sending feedback also actions it */
        $this->assertNotNull($this->feedback->actioned_at);
        $this->assertNotNull($this->feedback->actioned_comment);
        $this->assertEquals($this->privacc->id, $this->feedback->actioned_by_id);
    }

    /** @test */
    public function itReturnsTheSender()
    {
        $feedback = factory(Feedback::class)->create([
            'sent_by_id' => $this->privacc->id,
        ]);

        $this->assertEquals($this->privacc->id, $feedback->sender->id);
    }

    /** @test */
    public function itCorrectlyReturnsSentScope()
    {
        $this->feedback->markSent($this->privacc);

        $unsentFeedback = factory(Feedback::class)->create();

        $this->assertTrue(Feedback::sent()->get()->contains($this->feedback));
        $this->assertFalse(Feedback::sent()->get()->contains($unsentFeedback));
    }

    /** @test */
    public function itCalculatesActionedAtCorrectly()
    {
        $this->assertFalse($this->feedback->actioned);

        $this->feedback->markActioned($this->privacc);
        $this->assertTrue($this->feedback->fresh()->actioned);
    }
}
