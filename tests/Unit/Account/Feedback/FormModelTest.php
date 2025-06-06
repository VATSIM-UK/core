<?php

namespace Tests\Unit\Account\Feedback;

use App\Models\Contact;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Feedback\Question;
use App\Models\Mship\Feedback\Question\Type;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FormModelTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_creates_a_feedback_form()
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

    #[Test]
    public function it_creates_a_question_type()
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

    #[Test]
    public function it_creates_a_question()
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

    #[Test]
    public function it_saves_the_questions_for_a_form()
    {
        $form = factory(Form::class)->create();

        factory(Question::class, 3)->create([
            'form_id' => $form->id,
        ]);

        $this->assertEquals(3, $form->questions->count());
    }

    #[Test]
    public function it_returns_the_public_scope()
    {
        $notPublic = factory(Form::class)->create([
            'public' => 0,
        ]);

        $isPublic = factory(Form::class)->create([
            'public' => 1,
        ]);

        $this->assertTrue(Form::public()->get()->contains($isPublic));
        $this->assertFalse(Form::public()->get()->contains($notPublic));
    }

    #[Test]
    public function it_returns_a_contact_for_a_form()
    {
        $contact = factory(Contact::class)->create([
            'key' => 'KEY_DEPARTMENT',
            'name' => 'Department',
            'email' => 'department@vatsim.uk',
        ]);
        $form = factory(Form::class)->create([
            'contact_id' => $contact->id,
        ]);

        $this->assertEquals('Department', $form->contact->name);
    }

    #[Test]
    public function it_returns_responses_to_forms()
    {
        $form = factory(Form::class)->create();

        factory(Feedback::class, 3)->create([
            'form_id' => $form->id,
        ]);

        $this->assertEquals(3, $form->feedback->count());
    }
}
