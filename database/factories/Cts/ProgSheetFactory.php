<?php

namespace Database\Factories\Cts;

use App\Models\Cts\ProgSheet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<ProgSheet>
 */
class ProgSheetFactory extends Factory
{
    protected $model = ProgSheet::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'created_by' => 0,
            'created_date' => now(),
            'disabled' => 0,
        ];
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'disabled' => 1,
        ]);
    }
}
