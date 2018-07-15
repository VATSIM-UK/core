<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Feedback\Question;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FormModelTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
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

        // Perform assertion
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
}
