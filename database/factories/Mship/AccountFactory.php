<?php

namespace Database\Factories\Mship;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => rand(10000000, 99999999),
            'name_first' => fake()->firstName,
            'name_last' => fake()->lastName,
            'email' => fake()->email,
        ];
    }

    public function withQualification(): Factory
    {
        return $this->hasAttached(Qualification::factory()->atc()->create())->hasAttached(Qualification::factory()->pilot()->create());
    }
}
