<?php

declare(strict_types=1);

namespace Database\Factories\Training\Seminar;

use App\Models\Training\Seminar\Seminar;
use App\Models\Training\WaitingList;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeminarFactory extends Factory
{
    protected $model = Seminar::class;

    public function definition(): array
    {
        return [
            'waiting_list_id' => WaitingList::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'date' => now()->addWeeks(2)->format('Y-m-d'),
            'from' => '10:00',
            'to' => '16:00',
            'capacity' => fake()->numberBetween(5, 30),
            'invitation_expiry_days' => 7,
            'automatic_invitations_enabled' => false,
            'closed_at' => null,
            'created_by' => 1,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'closed_at' => now(),
        ]);
    }

    public function automatic(): static
    {
        return $this->state(fn (array $attributes) => [
            'automatic_invitations_enabled' => true,
        ]);
    }

    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => now()->subDays(2)->format('Y-m-d'),
        ]);
    }

    public function withCtsSession(): static
    {
        return $this->state(fn (array $attributes) => [
            'cts_group_session_id' => fake()->unique()->numberBetween(1, 99999),
        ]);
    }
}
