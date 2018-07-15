<?php

namespace Tests\Unit;

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
    public function itReturnsAContactForAForm()
    {
        $form = factory(Form::class)->create();

        $this->assertEquals('Privileged Access', $form->contact->name);
    }
}
