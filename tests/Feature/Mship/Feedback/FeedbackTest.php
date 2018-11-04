<?php

namespace Tests\Feature\Mship\Feedback;

use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Feedback\Question;
use App\Models\Mship\Feedback\Question\Type;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use DatabaseTransactions;

    private $account;
    private $form;

    public function setUp()
    {
        parent::setUp();

        $this->account = factory(Account::class)->create();
        $this->form = Form::find(1);
    }

    /** @test * */
    public function itCreatesAFeedbackForm()
    {
        $form = [
            'name' => 'My New Feedback Form',
            'slug' => 'mnff',
        ];

        $newForm = factory(Form::class)->create($form);

        $this->assertEquals('My New Feedback Form', $newForm->name);
        $this->assertEquals('mnff', $newForm->slug);
        $this->assertDatabaseHas('mship_feedback_forms', $form);
    }

    /** @test * */
    public function itCreatesAQuestionType()
    {
        $code = '<input name="%1" type="radio" style="margin-left: 20px;" value="%4" id="%1" %5> %3';

        $questionType = [
            'name' => 'radio',
            'code' => $code,
        ];

        $newQuestionType = factory(Type::class)->create($questionType);

        $this->assertEquals('radio', $newQuestionType->name);
        $this->assertEquals($code, $newQuestionType->code);
        $this->assertDatabaseHas('mship_feedback_question_types', $questionType);
    }

    /** @test * */
    public function itCreatesAQuestion()
    {
        $question = [
            'type_id' => factory(Type::class)->create()->id,
            'form_id' => factory(Form::class)->create()->id,
            'slug' => 'myquestion',
            'question' => 'This is a sample question.',
            'required' => 0,
            'sequence' => 1,
            'permanent' => 1,
        ];

        $newQuestion = factory(Question::class)->create($question);

        $this->assertEquals('myquestion', $newQuestion->slug);
        $this->assertEquals('This is a sample question.', $newQuestion->question);
        $this->assertDatabaseHas('mship_feedback_questions', $question);
    }

    /** @test * */
    public function itRedirectsFromFeedbackFormSelectorAsGuest()
    {
        $this->get(route('mship.feedback.new'))
            ->assertRedirect(route('login'));
    }

    /** @test * */
    public function itLoadsTheFeedbackFormSelector()
    {
        $this->actingAs($this->account, 'web')->get(route('mship.feedback.new'))
            ->assertSuccessful();
    }

    /** @test * */
    public function itRedirectsFromFeedbackFormAsGuest()
    {
        $this->get(route('mship.feedback.new.form', $this->form->id))
            ->assertRedirect(route('login'));
    }

    /** @test * */
    public function itLoadsTheFeedbackForm()
    {
        $this->actingAs($this->account, 'web')->get(route('mship.feedback.new.form', $this->form->id))
            ->assertSuccessful();
    }

//    /** @test * */
//    public function itAllowsSubmission()
//    {
//        //
//    }
//
//    /** @test * */
//    public function itAllowsCreationOfFormWithPermission()
//    {
//        //
//    }
//
//    /** @test * */
//    public function itAllowsViewingOfSubmissionWithPermission()
//    {
//        //
//    }
//
//    /** @test * */
//    public function itDoesNotAllowViewingOfSubmissionWithoutPermission()
//    {
//        //
//    }
}
