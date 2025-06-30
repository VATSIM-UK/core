<?php

namespace Database\Factories\Cts;

use App\Models\Cts\ValidationPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

class ValidationPositionFactory extends Factory
{
    protected $model = ValidationPosition::class;

    public function definition(): array
    {
        return [
            'position' => 'Heathrow (TWR)',
            'rts' => rand(1, 15),
            'min_rating' => rand(1, 12),
        ];
    }
}
