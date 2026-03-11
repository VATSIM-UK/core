<?php

namespace Database\Factories\Training\TrainingPlace;

use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TrainingPlaceOfferFactory extends Factory
{
    protected $model = TrainingPlaceOffer::class;

    public function definition(): array
    {
        return [
            'waiting_list_account_id' => null,
            'training_position_id' => null,
            'token' => Str::random(32),
            'status' => TrainingPlaceOfferStatus::Pending,
            'expires_at' => now()->addHours(84),
            'response_at' => null,
            'decline_reason' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => TrainingPlaceOfferStatus::Pending]);
    }

    public function accepted(): static
    {
        return $this->state([
            'status' => TrainingPlaceOfferStatus::Accepted,
            'response_at' => now(),
        ]);
    }

    public function declined(): static
    {
        return $this->state([
            'status' => TrainingPlaceOfferStatus::Declined,
            'response_at' => now(),
        ]);
    }

    public function rescinded(): static
    {
        return $this->state(['status' => TrainingPlaceOfferStatus::Rescinded]);
    }

    public function expired(): static
    {
        return $this->state([
            'status' => TrainingPlaceOfferStatus::Expired,
            'expires_at' => now()->subHour(),
        ]);
    }

    public function expiredAt(\Carbon\Carbon $date): static
    {
        return $this->state(['expires_at' => $date]);
    }
}
