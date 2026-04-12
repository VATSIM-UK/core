<?php

namespace Database\Factories\Training\TrainingPlace;

use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
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
            'training_position_id' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (TrainingPlace $trainingPlace): void {
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
}
