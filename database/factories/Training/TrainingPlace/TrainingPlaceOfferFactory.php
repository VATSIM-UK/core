<?php

namespace Database\Factories\Training\TrainingPlace;

use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Mship\Qualification;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TrainingPlaceOfferFactory extends Factory
{
    protected $model = TrainingPlaceOffer::class;

    public function definition(): array
    {
        return [
            'waiting_list_account_id' => null,
            'trainable_type' => null,
            'trainable_id' => null,
            'token' => Str::random(32),
            'status' => TrainingPlaceOfferStatus::Pending,
            'expires_at' => now()->addHours(84),
            'response_at' => null,
            'decline_reason' => null,
        ];
    }

    public function forTrainingPosition(TrainingPosition $trainingPosition): static
    {
        return $this->state([
            'trainable_type' => TrainingPosition::class,
            'trainable_id' => $trainingPosition->id,
        ]);
    }

    public function forQualification(Qualification $qualification): static
    {
        return $this->state([
            'trainable_type' => Qualification::class,
            'trainable_id' => $qualification->id,
        ]);
    }

    public function configure(): static
    {
        return $this->afterMaking(function (TrainingPlaceOffer $offer): void {
            if (! array_key_exists('training_position_id', $offer->getAttributes())) {
                return;
            }

            $legacyId = $offer->getAttribute('training_position_id');

            unset($offer->training_position_id);

            if ($legacyId && ! $offer->trainable_id) {
                $offer->trainable_type = TrainingPosition::class;
                $offer->trainable_id = $legacyId;
            }
        });
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
