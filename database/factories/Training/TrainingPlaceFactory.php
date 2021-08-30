<?php

namespace Database\Factories\Training;

use Carbon\Carbon;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Training\TrainingPlace\TrainingPosition;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;

class TrainingPlaceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TrainingPlace::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'training_position_id' => function() {
                return TrainingPosition::factory()->create()->id;
            },
            'account_id' => factory(Account::class)->create()->id,
            'offer_id' => TrainingPlaceOffer::factory()->create()->offer_id,
            'accepted_at' => Carbon::now()
        ];
    }
}
