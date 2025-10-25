<?php

namespace Database\Factories\Cts;

use App\Models\Cts\TheoryQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cts\TheoryQuestion>
 */
class TheoryQuestionFactory extends Factory
{
    protected $model = TheoryQuestion::class;

    public function definition()
    {
        return [
            'level' => $this->faker->randomElement(['S1', 'S2', 'S3', 'C1']),
            'question' => $this->faker->sentence(),
            'option_1' => $this->faker->word(),
            'option_2' => $this->faker->word(),
            'option_3' => $this->faker->word(),
            'option_4' => $this->faker->word(),
            'answer' => $this->faker->numberBetween(1, 4),
            'add_by' => 1,
            'add_date' => now(),
            'edit_by' => 1,
            'edit_date' => now(),
            'deleted' => 0,
            'status' => true,
        ];
    }
}
