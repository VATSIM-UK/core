<?php

namespace Database\Factories\Cts;

use App\Models\Cts\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cts\PracticalResult>
 */
class PracticalResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'examid' => $this->faker->randomNumber(3),
            'student_id' => Member::Factory()->create()->id,
            'exam' => $this->faker->randomElement(['OBS', 'TWR', 'APP', 'CTR']),
            'result' => $this->faker->randomElement(['P', 'F']),
            'date' => $this->faker->dateTime(),
        ];
    }
}
