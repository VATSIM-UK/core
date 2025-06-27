<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cts\TheoryManagement>
 */
class TheoryManagementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item' => $this->faker->randomElement(['theory_s1', 'theory_s1', 'theory_s3', 'theory_c1']),
            'setting' => 1,
        ];
    }
}
