<?php

namespace Database\Factories\Mship\Feedback;

use App\Models\Mship\Feedback\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormFactory extends Factory
{
    protected $model = Form::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => strtolower(fake()->word),
            'contact_id' => fake()->numberBetween(1, 3),
            'enabled' => 1,
            'targeted' => 1,
            'public' => 1,
        ];
    }

    public function atc(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'ATC Feedback',
            'slug' => 'atc',
        ]);
    }

    public function pilot(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Pilot Feedback',
            'slug' => 'pilot',
        ]);
    }
}
