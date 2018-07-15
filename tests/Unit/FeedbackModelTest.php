<?php

namespace Tests\Unit;

use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Feedback\Question;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FeedbackTest extends TestCase
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
}
