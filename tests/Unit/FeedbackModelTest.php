<?php

namespace Tests\Unit;

use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Feedback\Question;

/** @test * */
public function itSavesTheQuestionsForAForm()
{
    $form = factory(Form::class)->create();

    factory(Question::class, 3)->create([
        'form_id' => $form->id,
    ]);

    $this->assertEquals(3, $form->questions->count());
}