<?php

namespace Database\Factories;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'position_id' => Position::factory(),
            'member_id' => Account::factory(),
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ];
    }

    public function forEvent(): static
    {
        return $this->state(fn (array $attributes) => [
            'member_id' => null,
            'type' => Booking::TYPE_EVENT,
        ]);
    }

    public function forExam(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Booking::TYPE_EXAM,
        ]);
    }

    public function forMentoring(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Booking::TYPE_MENTORING,
        ]);
    }
}
