<?php

namespace Database\Factories\TeamSpeak;

use App\Models\Mship\Account;
use App\Models\TeamSpeak\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'registration_ip' => $this->faker->ipv4(),
            'last_ip' => $this->faker->ipv4(),
            'last_login' => $this->faker->dateTimeThisYear(),
            'last_os' => $this->faker->randomElement(['Windows', 'macOS', 'Linux']),
            // Generates a mock TeamSpeak Unique ID
            'uid' => $this->faker->regexify('[A-Za-z0-9+/]{27}='),
            'dbid' => $this->faker->numberBetween(1, 50000),
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }

    public function trashed(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => $this->faker->dateTimeThisYear(),
        ]);
    }
}
