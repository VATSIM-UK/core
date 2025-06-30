<?php

// use Faker\Generator as Faker;

// $factory->define(App\Models\Cts\ValidationPosition::class, function (Faker $faker) {
//     return [
//         'position' => 'Heathrow (TWR)',
//         'rts' => rand(1, 15),
//         'min_rating' => rand(1, 12),
//     ];
// });

namespace Database\Factories\Cts;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Cts\ValidationPosition;

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