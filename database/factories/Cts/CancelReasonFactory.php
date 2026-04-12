<?php

namespace Database\Factories\Cts;

use App\Models\Cts\CancelReason;
use App\Models\Cts\ExamBooking;
use Illuminate\Database\Eloquent\Factories\Factory;

class CancelReasonFactory extends Factory
{
    protected $model = CancelReason::class;

    public function definition(): array
    {
        return [
            'sesh_id' => ExamBooking::factory(),
            'sesh_type' => 'EX',
            'reason' => $this->faker->sentence(),
            'reason_by' => $this->faker->randomNumber(7, true),
            'date' => now(),
            'used' => 0,
        ];
    }
}
