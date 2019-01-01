<?php

namespace Tests\Unit\Mship\Feedback;

use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Feedback;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FeedbackModelTest extends TestCase
{
    use DatabaseTransactions;

    private $admin;
    private $feedback;

    public function setUp()
    {
        parent::setUp();

        $this->admin = factory(Account::class)->create();
        $this->feedback = factory(Feedback::class)->create();
    }

    /** @test * */
    public function itMarksTheFeedbackAsActioned()
    {
        $comment = 'Test';

        $this->assertNull($this->feedback->actioned_at);
        $this->assertNull($this->feedback->actioned_comment);
        $this->assertNull($this->feedback->actioned_by_id);

        $this->feedback->markActioned($this->admin, $comment);

        $this->assertNotNull($this->feedback->actioned_at);
        $this->assertNotNull($this->feedback->actioned_comment);
        $this->assertEquals($this->admin->id, $this->feedback->actioned_by_id);
    }

    /** @test * */
    public function itMarksTheFeedbackAsUnActioned()
    {
        $this->feedback->markActioned($this->admin);

        $this->feedback->markUnActioned();

        $this->assertNull($this->feedback->actioned_at);
        $this->assertNull($this->feedback->actioned_comment);
        $this->assertNull($this->feedback->actioned_by_id);
    }

    /** @test * */
    public function itMarksTheFeedbackAsSent()
    {
        $comment = 'Test';

        $this->assertNull($this->feedback->sent_at);
        $this->assertNull($this->feedback->sent_comment);
        $this->assertNull($this->feedback->sent_by_id);
        $this->assertNull($this->feedback->actioned_at);
        $this->assertNull($this->feedback->actioned_comment);
        $this->assertNull($this->feedback->actioned_by_id);

        $this->feedback->markSent($this->admin, $comment);

        $this->assertNotNull($this->feedback->sent_at);
        $this->assertEquals($comment, $this->feedback->sent_comment);
        $this->assertEquals($this->admin->id, $this->feedback->sent_by_id);

        /* Sending feedback also actions it */
        $this->assertNotNull($this->feedback->actioned_at);
        $this->assertNotNull($this->feedback->actioned_comment);
        $this->assertEquals($this->admin->id, $this->feedback->actioned_by_id);
    }

//    /** @test * */
//    public function itReturnsOptionsInJson()
//    {
//        //
//    }

    /** @test * */
    public function itReturnsTheSender()
    {
        $feedback = factory(Feedback::class)->create([
            'sent_by_id' => $this->admin->id,
        ]);

        $this->assertEquals($this->admin->id, $feedback->sender->id);
    }

    /** @test * */
    public function itCorrectlyReturnsSentScope()
    {
        $this->feedback->markSent($this->admin);

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
