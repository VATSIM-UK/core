<?php

namespace Database\Factories\VisitTransfer;

use App\Models\VisitTransfer\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacilityFactory extends Factory
{
    protected $model = Facility::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'training_team' => 'atc',
            'open' => true,
            'description' => $this->faker->paragraph,
        ];
    }

    public function visit(string $team = 'atc')
    {
        return $this->state(function (array $attributes) use ($team) {
            return [
                'training_team' => $team,
                'can_visit' => true,
            ];
        });
    }

    public function transfer(string $team = 'atc')
    {
        return $this->state(function (array $attributes) use ($team) {
            return [
                'training_team' => $team,
                'can_transfer' => true,
                'training_spaces' => 1,
                'training_required' => true,
            ];
        });
    }
}
