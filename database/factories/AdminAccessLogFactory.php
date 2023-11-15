<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdminAccessLog>
 */
class AdminAccessLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'accessor_account_id' => 1,
            'loggable_id' => 1,
            'loggable_type' => 'App\Models\User',
            'action' => 'View',
        ];
    }
}
