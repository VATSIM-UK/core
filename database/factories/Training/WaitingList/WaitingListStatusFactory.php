<?php
namespace Database\Factories\Training\WaitingList;

use App\Models\Training\WaitingList\WaitingListStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class WaitingListStatusFactory extends Factory
{
    protected $model = WaitingListStatus::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'retains_position' => true,
            'default' => false, // default false unless the "default" state is used
        ];
    }

    public function default(): self
    {
        return $this->state(fn () => [
            'default' => true,
        ]);
    }
}