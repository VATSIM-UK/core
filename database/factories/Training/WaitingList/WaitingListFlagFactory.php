<?php

namespace Database\Factories\Training\WaitingList;

use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListFlag;
use Illuminate\Database\Eloquent\Factories\Factory;

class WaitingListFlagFactory extends Factory
{
    protected $model = WaitingListFlag::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'list_id' => WaitingList::factory(),
            'default_value' => true,
        ];
    }
}
