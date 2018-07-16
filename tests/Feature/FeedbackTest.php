<?php

namespace Tests\Feature;

use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Feedback\Question;
use App\Models\Mship\Feedback\Question\Type;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Mship\Role;
use App\Models\Mship\Account;
use App\Models\Mship\State;

class FeedbackTest extends TestCase
{
    use DatabaseTransactions;

    private $admin;
    private $member;
    private $feedback;

    public function setUp()
    {
        parent::setUp();

        $this->admin = factory(Account::class)->create();
        $this->admin->roles()->attach(Role::find(1));
        $this->admin->addState(State::findByCode('DIVISION'));

        $this->member = factory(Account::class)->create();
        $this->member->addState(State::findByCode('DIVISION'));

        $this->feedback = factory(Feedback::class)->create();
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
        $this->actingAs($this->admin)->post(route('adm.mship.feedback.send', $this->feedback->id))
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    /** @test * */
    public function itDoesNotAllowSendingWithoutPermission()
    {
        $this->actingAs($this->member)->post(route('adm.mship.feedback.send', $this->feedback->id))
            ->assertStatus(403);
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
