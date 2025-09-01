<?php

namespace Database\Factories\Training;

use App\Models\Training\WaitingList;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WaitingListFactory extends Factory
{
    protected $model = WaitingList::class;

    public function definition(): array
    {
        $name = $this->faker->name;

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'department' => 'atc',
            'cts_theory_exam_level' => null,
            'feature_toggles' => [
                'check_atc_hours' => true,
            ],
            'requires_roster_membership' => false,
        ];
    }
}
