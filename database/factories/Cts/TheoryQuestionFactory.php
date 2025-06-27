<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cts\TheoryQuestion>
 */
class TheoryQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question' => $this->faker->sentence,
            'option_1' => $this->faker->word,
            'option_2' => $this->faker->word,
            'option_3' => $this->faker->word,
            'option_4' => $this->faker->word,
            'answer' => 1,
            'level' => $this->faker->randomElement(['S1', 'S2', 'S3', 'C1']),
            'add_by' => App\Models\Mship\Account::factory()->create()->id,
            'add_date' => now(),
            'edit_by' => App\Models\Mship\Account::factory()->create()->id,
            'edit_date' => now(),
            'deleted' => 0,
        ];
    }
}
