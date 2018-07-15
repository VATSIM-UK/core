<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Account;

class FeedbackSendTest extends TestCase
{
    use DatabaseTransactions;

    private $member;
    private $form;

    public function setUp()
    {
        parent::setUp();

        $member = factory(Account::class)->create();
        $form = factory(Form::class)->create();
    }

    /** @test * */
    public function itCreatesAFeedbackForm()
    {
        //
    }

    /** @test * */
    public function itCreatesAQuestionType()
    {
        //
    }

    /** @test * */
    public function itCreatesAQuestion()
    {
        //
    }

    /** @test * */
    public function itAllowsSubmission()
    {
        //
    }

    /** @test * */
    public function itAllowsCreationOfFormWithPermission()
    {
        //
    }

    /** @test * */
    public function itAllowsViewingOfSubmissionWithPermission()
    {
        //
    }

    /** @test * */
    public function itDoesNotAllowViewingOfSubmissionWithoutPermission()
    {
        //
    }

    /** @test * */
    public function itAllowsSendingWithPermission()
    {
        //
    }

    /** @test * */
    public function itShowsSentFormsToMember()
    {
        //
    }

    /** @test * */
    public function itDoesNotShowUnsentFormsToMember()
    {
        //
    }
}
