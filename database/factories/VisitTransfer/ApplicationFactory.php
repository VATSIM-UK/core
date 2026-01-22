<?php

namespace Database\Factories\VisitTransfer;

use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition()
    {
        return [
            'account_id' => Account::factory(),
        ];
    }

    public function visit(string $team = 'atc')
    {
        return $this->state(function (array $attributes) use ($team) {
            $facility = Facility::factory()->visit($team)->create();

            return [
                'type' => Application::TYPE_VISIT,
                'facility_id' => Facility::factory()->visit($team),
                'training_team' => $team,
                'status' => Application::STATUS_SUBMITTED,
                'statement' => fake()->paragraph,
            ];
        });
    }

    public function transfer(string $team = 'atc')
    {
        return $this->state(function (array $attributes) use ($team) {
            $facility = Facility::factory()->transfer($team)->create();

            return [
                'type' => Application::TYPE_TRANSFER,
                'facility_id' => Facility::factory()->transfer($team),
                'training_team' => $team,
                'status' => Application::STATUS_SUBMITTED,
                'statement' => fake()->paragraph,
            ];
        });
    }
}
