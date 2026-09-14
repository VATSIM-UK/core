<?php

namespace Database\Factories\Training\TrainingPlace;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList\WaitingListAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Training\TrainingPlace\TrainingPlace>
 */
class TrainingPlaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'waiting_list_account_id' => null,
            'trainable_type' => null,
            'trainable_id' => null,
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
        return $this->afterMaking(function (TrainingPlace $trainingPlace): void {
            $this->normaliseLegacyTrainingPosition($trainingPlace);

            if ($trainingPlace->account_id) {
                return;
            }

            if ($trainingPlace->waiting_list_account_id) {
                $waitingListAccount = WaitingListAccount::query()
                    ->withTrashed()
                    ->find($trainingPlace->waiting_list_account_id);

                if ($waitingListAccount) {
                    $trainingPlace->account_id = $waitingListAccount->account_id;

                    return;
                }
            }

            $trainingPlace->account_id = Account::factory()->createQuietly()->id;
        });
    }

    /**
     * Support the legacy `training_position_id` attribute by mapping it onto the
     * polymorphic `trainable` association.
     */
    private function normaliseLegacyTrainingPosition(TrainingPlace $trainingPlace): void
    {
        if (! array_key_exists('training_position_id', $trainingPlace->getAttributes())) {
            return;
        }

        $legacyId = $trainingPlace->getAttribute('training_position_id');

        unset($trainingPlace->training_position_id);

        if ($legacyId && ! $trainingPlace->trainable_id) {
            $trainingPlace->trainable_type = TrainingPosition::class;
            $trainingPlace->trainable_id = $legacyId;
        }
    }
}
