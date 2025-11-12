<?php

namespace Database\Factories\Cts;

use App\Models\Cts\TheoryAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cts\TheoryAnswer>
 */
class TheoryAnswerFactory extends Factory
{
    protected $model = TheoryAnswer::class;

    public function definition()
    {
        return [
            'theory_id' => null, // overide in test
            'question_id' => null, // overide in test
            'answer_given' => $this->faker->numberBetween(1, 4),
        ];
    }
}
