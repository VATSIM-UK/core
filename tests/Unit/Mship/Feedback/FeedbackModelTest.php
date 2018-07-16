<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Feedback\Question;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Mship\Account;

class FeedbackModelTest extends TestCase
{
    use DatabaseTransactions;

    private $admin;

    public function setUp()
    {
        parent::setUp();

        $this->admin = factory(Account::class)->create();
    }

    /** @test * */
    public function itMarksTheFeedbackAsActioned()
    {
        $feedback = factory(Feedback::class)->create();
        $comment = 'Test';

        $this->assertNull($feedback->actioned_at);
        $this->assertNull($feedback->actioned_comment);
        $this->assertNull($feedback->actioned_by_id);

        $feedback->markActioned($this->admin, $comment);

        $this->assertNotNull($feedback->actioned_at);
        $this->assertNotNull($feedback->actioned_comment);
        $this->assertEquals($this->admin->id, $feedback->actioned_by_id);
    }

    /** @test * */
    public function itMarksTheFeedbackAsUnActioned()
    {
        $feedback = factory(Feedback::class)->create();
        $feedback->markActioned($this->admin);

        $feedback->markUnActioned();

        $this->assertNull($feedback->actioned_at);
        $this->assertNull($feedback->actioned_comment);
        $this->assertNull($feedback->actioned_by_id);
    }

    /** @test * */
    public function itMarksTheFeedbackAsSent()
    {
        $feedback = factory(Feedback::class)->create();
        $comment = 'Test';

        $this->assertNull($feedback->sent_at);
        $this->assertNull($feedback->sent_comment);
        $this->assertNull($feedback->sent_by_id);
        $this->assertNull($feedback->actioned_at);
        $this->assertNull($feedback->actioned_comment);
        $this->assertNull($feedback->actioned_by_id);

        $feedback->markSent($this->admin, $comment);

        $this->assertNotNull($feedback->sent_at);
        $this->assertEquals($comment, $feedback->sent_comment);
        $this->assertEquals($this->admin->id, $feedback->sent_by_id);

        /* Sending feedback also actions it */
        $this->assertNotNull($feedback->actioned_at);
        $this->assertNotNull($feedback->actioned_comment);
        $this->assertEquals($this->admin->id, $feedback->actioned_by_id);
    }
}
