<?php

namespace Database\Factories\Training;

use App\Models\Mship\Account;
use App\Models\Training\Availability;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Availability>
 */
class AvailabilityFactory extends Factory
{
    protected $model = Availability::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = Carbon::parse(
            $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d').' '.$this->faker->time('H:i:s', '20:00:00')
        );

        return [
            'account_id' => Account::factory(),
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHours(rand(2, 4)),
        ];
    }

    public function forAccount(int $accountId): static
    {
        return $this->state(fn (array $attributes) => [
            'account_id' => $accountId,
        ]);
    }

    /**
     * Move the slot to a random day between today and the end of the window, keeping its length.
     */
    public function future(): static
    {
        return $this->state(function (array $attributes) {
            $startsAt = Carbon::parse($attributes['starts_at']);
            $minutes = (int) $startsAt->diffInMinutes(Carbon::parse($attributes['ends_at']));

            $start = Carbon::parse(
                $this->faker->dateTimeBetween('today', '+30 days')->format('Y-m-d').' '.$startsAt->format('H:i:s')
            );

            return [
                'starts_at' => $start,
                'ends_at' => $start->copy()->addMinutes($minutes),
            ];
        });
    }

    public function onDate(string $date): static
    {
        return $this->state(function (array $attributes) use ($date) {
            $shift = Carbon::parse($attributes['starts_at'])->startOfDay()->diffInDays(Carbon::parse($date)->startOfDay());

            return [
                'starts_at' => Carbon::parse($attributes['starts_at'])->addDays($shift),
                'ends_at' => Carbon::parse($attributes['ends_at'])->addDays($shift),
            ];
        });
    }

    /**
     * A `to` at or before `from` means the slot runs past midnight.
     */
    public function timeRange(string $from, string $to): static
    {
        return $this->state(function (array $attributes) use ($from, $to) {
            $date = Carbon::parse($attributes['starts_at'])->format('Y-m-d');

            $start = Carbon::parse($date.' '.$from, 'UTC');
            $end = Carbon::parse($date.' '.$to, 'UTC');

            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay();
            }

            return [
                'starts_at' => $start,
                'ends_at' => $end,
            ];
        });
    }
}
