<?php

namespace Tests\Unit\Mship\Feedback;

use App\Models\Contact;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Feedback\Question;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FormModelTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test * */
    public function itSavesTheQuestionsForAForm()
    {
        $form = factory(Form::class)->create();

        factory(Question::class, 3)->create([
            'form_id' => $form->id,
        ]);

        $this->assertEquals(3, $form->questions->count());
    }

    /** @test * */
    public function itReturnsThePublicScope()
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

    /** @test * */
    public function itReturnsAContactForAForm()
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

    /** @test * */
    public function itReturnsResponsesToForms()
    {
        $form = factory(Form::class)->create();

        factory(Feedback::class, 3)->create([
            'form_id' => $form->id,
        ]);

        $this->assertEquals(3, $form->feedback->count());
    }

//    /** @test * */
//    public function itReturnsARouteKeyName()
//    {
//        // assert routekeyname() works
//    }
}
