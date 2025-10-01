<?php

namespace Database\Factories\Cts;

use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Availability>
 */
class AvailabilityFactory extends Factory
{
    protected $model = Availability::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('now', '+30 days');
        $startTime = $this->faker->time('H:i:s', '20:00:00');
        $endTime = Carbon::createFromTimeString($startTime)->addHours(rand(2, 4))->format('H:i:s');

        return [
            'student_id' => Member::factory(),
            'date' => $date->format('Y-m-d'),
            'from' => $startTime,
            'to' => $endTime,
            'type' => 'S', // Student availability
        ];
    }

    /**
     * Create availability for a specific student
     */
    public function forStudent(int $studentId): static
    {
        return $this->state(fn (array $attributes) => [
            'student_id' => $studentId,
        ]);
    }

    /**
     * Create availability for today or later
     */
    public function future(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $this->faker->dateTimeBetween('today', '+30 days')->format('Y-m-d'),
        ]);
    }

    /**
     * Create availability for a specific date
     */
    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }

    /**
     * Create availability with specific time range
     */
    public function timeRange(string $from, string $to): static
    {
        return $this->state(fn (array $attributes) => [
            'from' => $from,
            'to' => $to,
        ]);
    }
}
