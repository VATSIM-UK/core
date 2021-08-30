<?php

namespace Database\Factories\Training\TrainingPlace;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;

class TrainingPlaceOfferFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TrainingPlaceOffer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'offer_id' => Uuid::uuid4()->toString(),
            'account_id' => factory(Account::class)->create()->id,
            'offered_by' => factory(Account::class)->create()->id,
            'expires_at' => Carbon::now()->addHours(72),
        ];
    }
}
