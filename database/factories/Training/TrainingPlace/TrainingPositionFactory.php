<?php

namespace Database\Factories\Training\TrainingPlace;

use App\Models\Cts\Position;
use App\Models\Station;
use App\Models\Training\TrainingPlace\TrainingPosition;
use App\Models\Training\WaitingList;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingPositionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TrainingPosition::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'station_id' => factory(Station::class)->create()->id,
            'cts_position_id' => factory(Position::class)->create()->id,
            'waiting_list_id' => factory(WaitingList::class)->create()->id,
            'places' => 1,
        ];
    }
}
