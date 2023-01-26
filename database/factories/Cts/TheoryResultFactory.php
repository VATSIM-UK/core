<?php

namespace Database\Factories\Cts;

use App\Models\Cts\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cts\TheoryResult>
 */
class TheoryResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'student_id' => factory(Member::class)->create()->id,
            'exam' => $this->faker->randomElement(['S1', 'S2', 'S3']),
            'pass' => 0,
        ];
    }
}
